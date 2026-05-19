<?php

namespace Modules\AIConsensus\Services;

class AIConsensusSchemaValidator
{
    public function validate(mixed $payload, ?array $schema): array
    {
        if (!$schema) {
            return ['valid' => true, 'errors' => []];
        }

        $errors = [];
        foreach (($schema['required'] ?? []) as $requiredKey) {
            if (!is_array($payload) || !array_key_exists($requiredKey, $payload)) {
                $errors[] = "Missing required key [$requiredKey].";
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }
}
