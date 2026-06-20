<?php

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiGatewayService
{
    public function health(): array
    {
        return $this->send('get', '/health');
    }

    public function generate(string $prompt, ?string $model = null): array
    {
        return $this->send('post', '/api/llm/generate', [
            'model' => $this->model($model),
            'prompt' => $prompt,
        ]);
    }

    public function chat(array $messages, ?string $model = null): array
    {
        return $this->send('post', '/api/llm/chat', [
            'model' => $this->model($model),
            'messages' => $messages,
        ]);
    }

    public function ocrImage(string $filePath, array $options = []): array
    {
        return $this->ocrFile('/api/ocr/image', $filePath, [
            'lang' => $options['lang'] ?? 'por+eng',
            'preprocess' => $options['preprocess'] ?? true,
        ]);
    }

    public function ocrPdf(string $filePath, array $options = []): array
    {
        return $this->ocrFile('/api/ocr/pdf', $filePath, [
            'lang' => $options['lang'] ?? 'por+eng',
            'preprocess' => $options['preprocess'] ?? true,
            'max_pages' => (int) ($options['max_pages'] ?? 5),
        ]);
    }

    public function visionAnalyze(string $filePath): array
    {
        return $this->multipartFile('/api/vision/analyze', $filePath, [], 'LSG AI gateway vision analyze request failed.');
    }

    public function visionPhash(string $filePath): array
    {
        return $this->multipartFile('/api/vision/phash', $filePath, [], 'LSG AI gateway vision pHash request failed.');
    }

    public function visionComparePhash(string $hashA, string $hashB): array
    {
        return $this->send('post', '/api/vision/compare-phash', [
            'hash_a' => $hashA,
            'hash_b' => $hashB,
        ]);
    }

    public function extractExpense(string $filePath, array $options = []): array
    {
        return $this->multipartFile('/api/documents/extract-expense', $filePath, [
            'lang' => $options['lang'] ?? 'por+eng',
            'preprocess' => $options['preprocess'] ?? true,
            'max_pages' => (int) ($options['max_pages'] ?? 5),
        ], 'LSG AI gateway expense extraction request failed.');
    }

    private function send(string $method, string $endpoint, array $payload = []): array
    {
        try {
            $response = match ($method) {
                'get' => $this->client()->get($endpoint),
                'post' => $this->client()->asJson()->post($endpoint, $payload),
                default => throw new RuntimeException('Unsupported AI gateway method.'),
            };

            return $response->throw()->json() ?? [];
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Unable to connect to the LSG AI gateway.', 0, $exception);
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $message = $status
                ? "LSG AI gateway request failed with HTTP status {$status}."
                : 'LSG AI gateway request failed.';

            throw new RuntimeException($message, 0, $exception);
        }
    }

    private function ocrFile(string $endpoint, string $filePath, array $payload): array
    {
        return $this->multipartFile($endpoint, $filePath, $payload, 'LSG AI gateway OCR request failed.');
    }

    private function multipartFile(string $endpoint, string $filePath, array $payload, string $requestErrorMessage): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException('AI gateway file is not available or readable.');
        }

        $resource = fopen($filePath, 'r');

        if ($resource === false) {
            throw new RuntimeException('Unable to open AI gateway file for reading.');
        }

        try {
            $response = $this->client()
                ->asMultipart()
                ->attach('file', $resource, basename($filePath))
                ->post($endpoint, $this->normalizeMultipartPayload($payload));

            return $response->throw()->json() ?? [];
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Unable to connect to the LSG AI gateway file service.', 0, $exception);
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $message = $status
                ? "{$requestErrorMessage} HTTP status {$status}."
                : $requestErrorMessage;

            throw new RuntimeException($message, 0, $exception);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    private function normalizeMultipartPayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_bool($value)) {
                $payload[$key] = $value ? 'true' : 'false';
            }
        }

        return $payload;
    }

    private function client(): PendingRequest
    {
        $gatewayUrl = rtrim((string) config('lsg_ai.gateway_url'), '/');
        $token = (string) config('lsg_ai.token');

        if ($gatewayUrl === '') {
            throw new RuntimeException('LSG AI gateway URL is not configured.');
        }

        if ($token === '' || $token === 'COLOCAR_TOKEN_AQUI') {
            throw new RuntimeException('LSG AI gateway token is not configured.');
        }

        return Http::baseUrl($gatewayUrl)
            ->acceptJson()
            ->timeout((int) config('lsg_ai.timeout', 180))
            ->withHeaders([
                'x-lsg-ai-token' => $token,
            ]);
    }

    private function model(?string $model): string
    {
        return $model ?: (string) config('lsg_ai.default_model', 'qwen2.5:7b');
    }
}
