<?php

namespace Modules\AIConsensus\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\AIConsensus\Jobs\ProcessAIConsensusRunJob;
use Modules\AIConsensus\Models\AIConsensusRun;

class AIConsensusGateway
{
    public function __construct(
        protected AIConsensusTemplateResolver $templateResolver,
        protected AIConsensusRunService $runService,
    ) {
    }

    public function createRun(array $payload): AIConsensusRun
    {
        $data = $this->validate($payload);
        $template = $this->templateResolver->resolve($data['template_key'], $data['output_type']);

        if (!$template) {
            throw ValidationException::withMessages([
                'template_key' => 'AI Consensus template not found or inactive: ' . $data['template_key'],
            ]);
        }

        $run = $this->runService->create($data, $template);

        if ((bool) data_get($data, 'options.async', true)) {
            ProcessAIConsensusRunJob::dispatch($run->id, true)->afterCommit();
        } else {
            $this->runService->process($run);
        }

        return $run->fresh(['template', 'messages']);
    }

    protected function validate(array $payload): array
    {
        $validator = Validator::make($payload, [
            'source_module' => ['required', 'string', 'max:80'],
            'source_type' => ['required', 'string', 'max:80'],
            'source_id' => ['nullable'],
            'template_key' => ['required', 'string', 'max:150'],
            'output_type' => ['required', 'string', 'max:80'],
            'input_payload' => ['required', 'array'],
            'options' => ['nullable', 'array'],
            'requested_by' => ['nullable', 'integer'],
            'title' => ['nullable', 'string', 'max:180'],
            'message' => ['nullable', 'string'],
        ]);

        return $validator->validate();
    }
}
