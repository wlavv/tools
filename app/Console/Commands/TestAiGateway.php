<?php

namespace App\Console\Commands;

use App\Services\AI\AiGatewayService;
use Illuminate\Console\Command;
use Throwable;

class TestAiGateway extends Command
{
    protected $signature = 'lsg:ai-test {prompt?}';

    protected $description = 'Test the LSG AI gateway health and text generation endpoints.';

    public function handle(AiGatewayService $ai): int
    {
        $prompt = $this->argument('prompt')
            ?: 'Responde em português europeu numa frase: o Laravel está ligado ao servidor AI LSG?';

        try {
            $this->info('Checking LSG AI gateway health...');
            $this->line($this->formatPayload($ai->health()));

            $this->newLine();
            $this->info('Generating response...');
            $this->line($this->formatPayload($ai->generate($prompt)));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function formatPayload(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }
}
