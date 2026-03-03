<?php

namespace App\Modules\AiConsensus\Services\Prompt;

class PromptTemplates
{
    public static function all(): array
    {
        return [
            'module_scaffold_v1' => [
                'label' => 'Module scaffold (architecture-first)',
                'system' => self::systemCommon(),
                'draft_instructions' => self::draftModuleScaffold(),
                'integrator_instructions' => self::integratorModuleScaffold(),
            ],
            'debug_triage_v1' => [
                'label' => 'Debug triage (logs-first)',
                'system' => self::systemCommon(),
                'draft_instructions' => self::draftDebugTriage(),
                'integrator_instructions' => self::integratorDebugTriage(),
            ],
        ];
    }

    public static function get(string $key): array
    {
        $all = self::all();
        return $all[$key] ?? $all['module_scaffold_v1'];
    }

    private static function systemCommon(): string
    {
        return "You are a senior software architect. Be precise, implementation-oriented, and avoid fluff.";
    }

    private static function draftModuleScaffold(): string
    {
        return <<<TXT
Output ONLY the base functional scaffold, not a full implementation.

Required sections:
1) Assumptions
2) Folder/module structure
3) DB schema (tables + indexes)
4) Core services/classes (names + responsibilities)
5) Entry points (routes/controllers/hooks/jobs)
6) Security & operational notes
7) Step-by-step implementation plan (short)

Constraints:
- Keep it concise and directly implementable.
- Max ~900 words.
TXT;
    }

    private static function integratorModuleScaffold(): string
    {
        return <<<TXT
You are the integrator. You received two expert drafts.

Tasks:
1) Extract the best parts of each draft.
2) Resolve contradictions (brief rationale).
3) Produce ONE consolidated answer optimized for implementation.

Output format:
- Final consolidated scaffold
- Key decisions (max 10 bullets)
- Implementation steps (numbered, max 15)
- Risks + mitigations
TXT;
    }

    private static function draftDebugTriage(): string
    {
        return <<<TXT
You are diagnosing a technical issue.

Required:
- Hypotheses (ordered)
- Minimal reproduction steps
- Instrumentation/logging to add
- Fix plan (incremental)
- Verification checklist
Keep it short and practical.
TXT;
    }

    private static function integratorDebugTriage(): string
    {
        return <<<TXT
Merge the two diagnostics into ONE plan. Prefer the plan with clearer reproduction + verification.
Output: single consolidated triage plan with ordered steps.
TXT;
    }
}