<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Infrastructure\AiBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InfrastructureAiBackupController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware(function (Request $request, $next) {
            abort_unless($this->canManageBackups($request->user()?->id), 403, 'Sem permissao para gerir backups AI.');

            return $next($request);
        });
    }

    public function index(AiBackupService $backups)
    {
        $this->preparePageMeta('AI Server Backups', 'index');

        $error = null;
        $items = [];

        try {
            $items = $backups->listBackups()['backups'] ?? [];
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }

        return view('admin.infrastructure.ai-backups.index', [
            'backups' => $items,
            'error' => $error,
        ]);
    }

    public function show(string $filename, AiBackupService $backups)
    {
        $filename = $backups->validateFilename($filename);
        $this->preparePageMeta('Backup AI', 'show', $filename);

        $details = [];
        $checksum = [];
        $manifest = [];
        $errors = [];

        try {
            $details = $backups->getBackupDetails($filename);
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }

        try {
            $checksum = $backups->getChecksum($filename);
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }

        try {
            $manifest = $backups->getManifest($filename);
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }

        return view('admin.infrastructure.ai-backups.show', [
            'filename' => $filename,
            'details' => $details,
            'checksum' => $checksum,
            'manifest' => $manifest,
            'errors' => array_unique($errors),
        ]);
    }

    public function create(AiBackupService $backups)
    {
        try {
            $result = $backups->createBackup();

            return redirect()
                ->route('admin.infrastructure.ai-backups.index')
                ->with('success', $result['message'] ?? 'Backup manual criado.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function download(string $filename, AiBackupService $backups)
    {
        try {
            $download = $backups->downloadBackup($filename);

            return response()
                ->download($download['path'], $download['filename'], [
                    'Content-Type' => $download['content_type'],
                    'X-Content-Type-Options' => 'nosniff',
                ])
                ->deleteFileAfterSend(true);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function checksum(string $filename, AiBackupService $backups)
    {
        try {
            $result = $backups->getChecksum($filename);
            $status = (string) data_get($result, 'status', data_get($result, 'valid', false) ? 'ok' : 'unknown');

            return back()->with('success', 'Validacao de checksum: ' . strtoupper($status));
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function manifest(string $filename, AiBackupService $backups)
    {
        try {
            $result = $backups->getManifest($filename);
            $content = data_get($result, 'manifest', data_get($result, 'content', ''));

            if (is_array($content)) {
                $content = implode("\n", array_map(
                    fn ($line) => is_scalar($line)
                        ? (string) $line
                        : json_encode($line, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    $content
                ));
            }

            return response((string) $content, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function logs(Request $request, AiBackupService $backups)
    {
        $this->preparePageMeta('AI Backup Logs', 'logs');

        $type = (string) $request->query('type', 'backup');
        $lines = (int) $request->query('lines', 100);
        $result = [];
        $error = null;

        try {
            $result = $backups->getLogs($type, $lines);
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }

        return view('admin.infrastructure.ai-backups.logs', [
            'type' => in_array($type, ['backup', 'cron'], true) ? $type : 'backup',
            'lines' => max(1, min($lines, 500)),
            'result' => $result,
            'error' => $error,
        ]);
    }

    public function destroy(string $filename, AiBackupService $backups)
    {
        try {
            $backups->deleteBackup($filename);

            return redirect()
                ->route('admin.infrastructure.ai-backups.index')
                ->with('success', 'Backup eliminado.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    private function canManageBackups(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        if (in_array($userId, config('permission-role-manager.route_access_super_user_ids', []), true)) {
            return true;
        }

        if (!Schema::hasTable('permission_roles') || !Schema::hasTable('permission_user_role')) {
            return $userId === 1;
        }

        return DB::table('permission_roles')
            ->join('permission_user_role', 'permission_roles.id', '=', 'permission_user_role.permission_role_id')
            ->where('permission_user_role.user_id', $userId)
            ->whereIn('permission_roles.slug', ['super-admin', 'admin', 'technical-admin', 'technical_admin'])
            ->where('permission_roles.is_active', true)
            ->whereNull('permission_roles.deleted_at')
            ->exists();
    }

    private function preparePageMeta(string $title, string $context = 'index', ?string $filename = null): void
    {
        $this->setPageTitle($title);
        $this->setBreadcrumbs([
            [
                'label' => 'Dashboard',
                'url' => route('dashboard.index'),
                'translate' => false,
            ],
            [
                'label' => 'LSG',
                'url' => route('lsg.index'),
                'translate' => false,
            ],
            [
                'label' => 'Infraestrutura',
                'url' => route('lsg.infrastructure'),
                'translate' => false,
            ],
            [
                'label' => 'AI Server Backups',
                'url' => $context === 'index' ? null : route('admin.infrastructure.ai-backups.index'),
                'translate' => false,
            ],
        ]);

        if ($context !== 'index') {
            $this->addBreadcrumb($context === 'logs' ? 'Logs' : ($filename ?: $title), null, [], false);
        }

        $this->setActions([]);
        $this->disableDefaultAction('new');
        $this->disableDefaultAction('back');
        $this->disableDefaultAction('edit');
        $this->disableDefaultAction('delete');
        $this->disableDefaultAction('show');
        $this->disableDefaultAction('save');

        if ($context === 'index') {
            $this->addAction([
                'key' => 'create_backup',
                'label' => 'Criar backup agora',
                'name' => 'Criar backup agora',
                'icon' => 'fa-solid fa-plus',
                'url' => route('admin.infrastructure.ai-backups.create'),
                'type' => 'form',
                'method' => 'POST',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ]);

            $this->addAction([
                'key' => 'backup_logs',
                'label' => 'Logs',
                'name' => 'Logs',
                'icon' => 'fa-solid fa-file-lines',
                'url' => route('admin.infrastructure.ai-backups.logs'),
                'type' => 'link',
                'class' => 'lsg-action-btn lsg-action-btn--primary',
            ]);
        }

        if ($context === 'show' && $filename) {
            $this->addAction([
                'key' => 'download_backup',
                'label' => 'Download',
                'name' => 'Download',
                'icon' => 'fa-solid fa-download',
                'url' => route('admin.infrastructure.ai-backups.download', $filename),
                'type' => 'link',
                'class' => 'lsg-action-btn lsg-action-btn--primary',
            ]);

            $this->addAction([
                'key' => 'checksum_backup',
                'label' => 'Validar checksum',
                'name' => 'Validar checksum',
                'icon' => 'fa-solid fa-check',
                'url' => route('admin.infrastructure.ai-backups.checksum', $filename),
                'type' => 'form',
                'method' => 'POST',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ]);

            $this->addAction([
                'key' => 'manifest_backup',
                'label' => 'Manifest',
                'name' => 'Manifest',
                'icon' => 'fa-solid fa-file-lines',
                'url' => route('admin.infrastructure.ai-backups.manifest', $filename),
                'type' => 'link',
                'class' => 'lsg-action-btn lsg-action-btn--primary',
            ]);

            $this->addAction([
                'key' => 'delete_backup',
                'label' => 'Eliminar',
                'name' => 'Eliminar',
                'icon' => 'fa-solid fa-trash',
                'url' => route('admin.infrastructure.ai-backups.destroy', $filename),
                'type' => 'delete',
                'method' => 'DELETE',
                'confirm' => 'Eliminar este backup e ficheiros associados?',
                'class' => 'lsg-action-btn lsg-action-btn--danger',
            ]);
        }
    }
}
