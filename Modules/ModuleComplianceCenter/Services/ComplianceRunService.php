<?php

namespace Modules\ModuleComplianceCenter\Services;

use Modules\ModuleComplianceCenter\Models\ComplianceRun;
use Modules\ModuleComplianceCenter\Models\ComplianceRunResult;
use Modules\ModuleComplianceCenter\Models\ComplianceRunValidator;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Throwable;

class ComplianceRunService
{
    public function __construct(
        private readonly ComplianceValidatorRegistry $registry,
        private readonly ComplianceResultNormalizer $normalizer,
        private readonly ComplianceScoringService $scoring,
        private readonly ComplianceReportService $reports,
    ) {
    }

    public function execute(ComplianceRun $run): ComplianceRun
    {
        $run->update(['status' => 'processing', 'started_at' => now(), 'error_message' => null]);
        $requested = $run->options['validators'] ?? null;
        $validators = $this->registry->active(is_array($requested) ? $requested : null);

        foreach ($validators as $validator) {
            $this->executeValidator($run, $validator);
        }

        $score = $this->scoring->calculateFinalScore($run->fresh('validators'));
        $finalStatus = $this->scoring->finalStatus($run, $score);
        $summary = $this->scoring->summarize($run);
        $scoreColumns = $this->scoreColumns($run);

        $run->update(array_merge($summary, $scoreColumns, [
            'status' => 'completed',
            'final_status' => $finalStatus,
            'final_score' => $score,
            'finished_at' => now(),
        ]));

        if (($run->options['generate_report'] ?? true) === true) {
            $this->reports->generate($run->fresh(['validators', 'results']));
        }

        $run->module?->update([
            'last_run_id' => $run->id,
            'last_status' => $finalStatus,
            'last_score' => $score,
            'last_checked_at' => now(),
        ]);

        return $run->fresh(['validators', 'results', 'report']);
    }

    private function executeValidator(ComplianceRun $run, array $validator): void
    {
        $runValidator = ComplianceRunValidator::create([
            'run_id' => $run->id,
            'validator_key' => $validator['key'],
            'validator_name' => $validator['label'],
            'validator_module' => $validator['module'],
            'status' => 'processing',
            'weight' => $validator['weight'],
            'started_at' => now(),
        ]);

        if (!$validator['available']) {
            $this->storeNormalized($run, $runValidator, $this->normalizer->unavailable($validator), 'unavailable');
            return;
        }

        try {
            $context = new ModuleValidationContext(
                moduleName: $run->module_name,
                modulePath: $run->module_path,
                sourceType: $run->source_type,
                sourceId: $run->source_id,
                options: $run->options ?? [],
                requestedBy: $run->requested_by,
            );

            $result = app($validator['service'])->validate($context);
            $normalized = $this->normalizer->normalize($result, $validator['key']);
            $this->storeNormalized($run, $runValidator, $normalized, $this->mapValidatorStatus($normalized['status']));
        } catch (Throwable $exception) {
            $this->storeNormalized($run, $runValidator, $this->normalizer->exception($validator, $exception), 'error', $exception->getMessage());
        }
    }

    private function storeNormalized(ComplianceRun $run, ComplianceRunValidator $runValidator, array $normalized, string $status, ?string $error = null): void
    {
        foreach ($normalized['findings'] as $finding) {
            ComplianceRunResult::create(array_merge($finding, [
                'run_id' => $run->id,
                'run_validator_id' => $runValidator->id,
                'validator_key' => $runValidator->validator_key,
            ]));
        }

        $findings = collect($normalized['findings']);
        $runValidator->update([
            'status' => $status,
            'score' => $normalized['score'],
            'findings_count' => $findings->count(),
            'failed_count' => $findings->where('status', 'failed')->count(),
            'warning_count' => $findings->where('status', 'warning')->count(),
            'blocker_count' => $findings->where('severity', 'blocker')->count(),
            'finished_at' => now(),
            'error_message' => $error,
            'raw_result' => $normalized['raw'],
        ]);
    }

    private function mapValidatorStatus(string $status): string
    {
        return match ($status) {
            'passed' => 'passed',
            'failed' => 'failed',
            'warning' => 'warning',
            'skipped' => 'skipped',
            default => 'manual_review_required',
        };
    }

    private function scoreColumns(ComplianceRun $run): array
    {
        return $run->validators()->get()
            ->mapWithKeys(fn ($validator) => [($validator->validator_key . '_score') => $validator->score])
            ->only(['structure_score', 'design_score', 'security_score', 'integration_score', 'health_score'])
            ->all();
    }
}
