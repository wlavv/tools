<?php

namespace Modules\AIConsensus\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AIConsensus\Models\AIProviderCredential;

class AIConsensusSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'anthropic' => [
                'label' => 'Claude',
                'base_url' => config('ai_consensus.providers.anthropic.api_base'),
                'default_model' => config('ai_consensus.providers.anthropic.default_model'),
            ],
            'gemini' => [
                'label' => 'Gemini',
                'base_url' => config('ai_consensus.providers.gemini.api_base'),
                'default_model' => config('ai_consensus.providers.gemini.default_model'),
            ],
            'openai' => [
                'label' => 'OpenAI',
                'base_url' => config('ai_consensus.providers.openai.api_base'),
                'default_model' => config('ai_consensus.providers.openai.default_model'),
            ],
        ];

        foreach ($defaults as $provider => $data) {
            AIProviderCredential::updateOrCreate(
                ['provider' => $provider],
                [
                    'label' => $data['label'],
                    'base_url' => $data['base_url'],
                    'default_model' => $data['default_model'],
                    'is_active' => true,
                    'meta' => [
                        'seeded' => true,
                    ],
                ]
            );
        }
    }
}
