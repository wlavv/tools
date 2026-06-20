<?php

namespace App\Services\Infrastructure;

use App\Models\InfrastructureBackupLog;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class AiBackupService
{
    private const FILENAME_PATTERN = '/\Alsg-ai-stack-backup-\d{8}_\d{6}(?:\.tar\.gz|\.tar\.gz\.sha256|\.manifest\.txt)\z/';

    public function listBackups(): array
    {
        return $this->requestWithAudit('list', null, function () {
            $payload = $this->client()->get('/api/admin/backups')->throw()->json() ?? [];

            return [
                'backups' => $this->normalizeBackups(data_get($payload, 'backups', $payload)),
                'payload' => $payload,
            ];
        });
    }

    public function createBackup(): array
    {
        return $this->requestWithAudit('create', null, function () {
            return $this->client()->post('/api/admin/backups/create')->throw()->json() ?? [];
        });
    }

    public function getBackupDetails(string $filename): array
    {
        $filename = $this->validateFilename($filename);

        return $this->requestWithAudit('view', $filename, function () use ($filename) {
            $payload = $this->client()->get('/api/admin/backups')->throw()->json() ?? [];
            $backups = $this->normalizeBackups(data_get($payload, 'backups', $payload));

            return collect($backups)->firstWhere('filename', $filename) ?? [
                'filename' => $filename,
                'status' => 'not_listed',
            ];
        });
    }

    public function downloadBackup(string $filename): array
    {
        $filename = $this->validateFilename($filename);

        if (!str_ends_with($filename, '.tar.gz')) {
            throw new RuntimeException('Only backup archives can be downloaded.');
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'lsg_ai_backup_');

        if ($temporaryPath === false) {
            throw new RuntimeException('Unable to allocate temporary backup download file.');
        }

        return $this->requestWithAudit('download', $filename, function () use ($filename, $temporaryPath) {
            try {
                $response = $this->client()
                    ->sink($temporaryPath)
                    ->get("/api/admin/backups/{$filename}/download")
                    ->throw();
            } catch (RequestException $exception) {
                if (!in_array($exception->response?->status(), [404, 405], true)) {
                    throw $exception;
                }

                $response = $this->client()
                    ->sink($temporaryPath)
                    ->get("/api/admin/backups/{$filename}")
                    ->throw();
            }

            return [
                'path' => $temporaryPath,
                'filename' => $filename,
                'size' => is_file($temporaryPath) ? filesize($temporaryPath) : null,
                'content_type' => $response->header('Content-Type') ?: 'application/gzip',
            ];
        });
    }

    public function getChecksum(string $filename): array
    {
        $filename = $this->validateFilename($filename);

        return $this->requestWithAudit('checksum', $filename, function () use ($filename) {
            return $this->client()->get("/api/admin/backups/{$filename}/checksum")->throw()->json() ?? [];
        });
    }

    public function getManifest(string $filename): array
    {
        $filename = $this->validateFilename($filename);

        return $this->requestWithAudit('manifest', $filename, function () use ($filename) {
            return $this->client()->get("/api/admin/backups/{$filename}/manifest")->throw()->json() ?? [];
        });
    }

    public function getLogs(string $type = 'backup', int $lines = 100): array
    {
        $type = in_array($type, ['backup', 'cron'], true) ? $type : 'backup';
        $lines = max(1, min($lines, 500));

        return $this->requestWithAudit('logs', null, function () use ($type, $lines) {
            return $this->client()->get('/api/admin/backups/logs', [
                'type' => $type,
                'lines' => $lines,
            ])->throw()->json() ?? [];
        }, ['type' => $type, 'lines' => $lines]);
    }

    public function deleteBackup(string $filename): array
    {
        $filename = $this->validateFilename($filename);

        return $this->requestWithAudit('delete', $filename, function () use ($filename) {
            return $this->client()->delete("/api/admin/backups/{$filename}")->throw()->json() ?? [];
        });
    }

    public function validateFilename(string $filename): string
    {
        $original = trim($filename);

        if ($original === '' || str_contains($original, '../') || str_contains($original, '..\\')) {
            throw new RuntimeException('Invalid backup filename.');
        }

        if (str_contains($original, '/') || str_contains($original, '\\') || preg_match('/^[a-zA-Z]:/', $original)) {
            throw new RuntimeException('Backup filename must not be a path.');
        }

        $filename = basename($original);

        if (!preg_match(self::FILENAME_PATTERN, $filename)) {
            throw new RuntimeException('Backup filename is not allowed.');
        }

        return $filename;
    }

    private function requestWithAudit(string $action, ?string $filename, callable $callback, array $payload = []): array
    {
        try {
            $result = $callback();
            $this->audit($action, $filename, 'ok', null, $result, $payload);

            return $result;
        } catch (ConnectionException $exception) {
            $message = 'Unable to connect to the LSG AI admin backup API.';
            $this->audit($action, $filename, 'error', $message, [], $payload);

            throw new RuntimeException($message, 0, $exception);
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $message = $status
                ? "LSG AI admin backup API request failed with HTTP status {$status}."
                : 'LSG AI admin backup API request failed.';
            $this->audit($action, $filename, 'error', $message, [], $payload);

            throw new RuntimeException($message, 0, $exception);
        } catch (Throwable $exception) {
            $this->audit($action, $filename, 'error', $exception->getMessage(), [], $payload);

            throw $exception;
        }
    }

    private function client(): PendingRequest
    {
        $gatewayUrl = rtrim((string) config('lsg_ai.gateway_url'), '/');
        $token = (string) config('lsg_ai.admin_token');

        if ($gatewayUrl === '') {
            throw new RuntimeException('LSG AI gateway URL is not configured.');
        }

        if ($token === '' || $token === 'COLOCAR_TOKEN_ADMIN_AQUI') {
            throw new RuntimeException('LSG AI admin token is not configured.');
        }

        return Http::baseUrl($gatewayUrl)
            ->acceptJson()
            ->timeout((int) config('lsg_ai.backup_timeout', 300))
            ->withHeaders([
                'x-lsg-ai-admin-token' => $token,
            ]);
    }

    private function normalizeBackups(mixed $backups): array
    {
        return collect(is_array($backups) ? $backups : [])
            ->map(function ($backup) {
                $row = is_array($backup) ? $backup : ['filename' => (string) $backup];
                $filename = (string) ($row['filename'] ?? $row['name'] ?? '');

                return array_merge($row, [
                    'filename' => $filename,
                    'created_at_from_name' => $this->dateFromFilename($filename),
                    'size' => $row['size'] ?? $row['bytes'] ?? null,
                    'checksum_exists' => (bool) ($row['checksum_exists'] ?? $row['has_checksum'] ?? false),
                    'manifest_exists' => (bool) ($row['manifest_exists'] ?? $row['has_manifest'] ?? false),
                    'validation_status' => $row['validation_status'] ?? $row['status'] ?? 'unknown',
                ]);
            })
            ->filter(fn ($backup) => $backup['filename'] !== '')
            ->values()
            ->all();
    }

    private function dateFromFilename(string $filename): ?string
    {
        if (!preg_match('/lsg-ai-stack-backup-(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})/', $filename, $matches)) {
            return null;
        }

        return "{$matches[1]}-{$matches[2]}-{$matches[3]} {$matches[4]}:{$matches[5]}:{$matches[6]}";
    }

    private function audit(
        string $action,
        ?string $filename,
        string $status,
        ?string $message = null,
        array $result = [],
        array $payload = []
    ): void {
        if (!Schema::hasTable('infrastructure_backup_logs')) {
            return;
        }

        InfrastructureBackupLog::query()->create([
            'server_name' => 'LSG AI Server',
            'server_type' => 'ai',
            'action' => $action,
            'backup_filename' => $filename,
            'backup_size' => data_get($result, 'size', data_get($result, 'backup.size')),
            'checksum' => data_get($result, 'checksum', data_get($result, 'backup.checksum')),
            'status' => $status,
            'message' => $message ?: data_get($result, 'message'),
            'requested_by' => auth()->id(),
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 512),
            'payload' => $this->safeAuditPayload($payload ?: $result),
        ]);
    }

    private function safeAuditPayload(array $payload): array
    {
        unset($payload['path'], $payload['content'], $payload['token'], $payload['admin_token']);

        if (isset($payload['payload']) && is_array($payload['payload'])) {
            unset($payload['payload']['token'], $payload['payload']['admin_token']);
        }

        return $payload;
    }
}
