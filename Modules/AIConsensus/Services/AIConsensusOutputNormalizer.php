<?php

namespace Modules\AIConsensus\Services;

use Modules\AIConsensus\Models\AIConsensusRun;

class AIConsensusOutputNormalizer
{
    public function __construct(protected AIConsensusSchemaValidator $schemaValidator)
    {
    }

    public function normalize(AIConsensusRun $run, string $content): array
    {
        $format = (string) data_get($run->options, 'return_format', data_get(config('ai-consensus-output-types'), $run->output_type . '.expected_format', 'json'));
        $jsonPayload = null;

        if ($format === 'json') {
            $jsonPayload = json_decode($content, true);
            if (!is_array($jsonPayload) && preg_match('/\\{.*\\}/s', $content, $match)) {
                $jsonPayload = json_decode($match[0], true);
            }
        }

        $validation = $this->schemaValidator->validate($jsonPayload, $run->template?->expected_output_schema);

        return [
            'format' => $format,
            'content' => $content,
            'json_payload' => is_array($jsonPayload) ? $jsonPayload : null,
            'schema_valid' => $validation['valid'],
            'validation_errors' => $validation['errors'],
        ];
    }
}
