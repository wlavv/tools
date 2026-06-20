<?php

namespace App\Services\AI;

use JsonException;
use RuntimeException;

class ExpenseExtractionService
{
    public function __construct(private readonly AiGatewayService $gateway)
    {
    }

    public function extractFromOcrText(string $ocrText): array
    {
        $response = $this->gateway->chat([
            [
                'role' => 'system',
                'content' => 'Devolve apenas JSON valido, sem markdown, sem explicacoes.',
            ],
            [
                'role' => 'user',
                'content' => $this->prompt($ocrText),
            ],
        ]);

        $raw = (string) (
            data_get($response, 'response')
            ?? data_get($response, 'message.content')
            ?? data_get($response, 'content')
            ?? data_get($response, 'text')
            ?? ''
        );

        try {
            $decoded = json_decode($this->cleanJson($raw), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('AI expense extraction returned invalid JSON.', 0, $exception);
        }

        return [
            'status' => 'ok',
            'data' => $decoded,
            'raw_response' => $raw,
            'gateway_response' => $response,
        ];
    }

    private function prompt(string $ocrText): string
    {
        return <<<PROMPT
Analisa o seguinte texto OCR de uma fatura/recibo e devolve apenas JSON valido, sem markdown.

Campos:
{
  "supplier_name": null,
  "supplier_vat": null,
  "invoice_number": null,
  "invoice_date": null,
  "currency": "EUR",
  "subtotal": null,
  "tax_amount": null,
  "total": null,
  "document_type": "invoice",
  "category_suggestion": null,
  "confidence": 0.0,
  "notes": null
}

Texto OCR:
{$ocrText}
PROMPT;
    }

    private function cleanJson(string $raw): string
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*```$/', '', $raw) ?? $raw;

        return trim($raw);
    }
}
