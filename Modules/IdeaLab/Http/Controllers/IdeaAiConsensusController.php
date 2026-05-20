<?php

namespace Modules\IdeaLab\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\IdeaLab\Http\Requests\RunAiConsensusRequest;
use Modules\IdeaLab\Models\Idea;
use Modules\IdeaLab\Models\IdeaAiMessage;
use Modules\IdeaLab\Models\IdeaAiTemplate;
use Modules\IdeaLab\Services\AiConsensus\IdeaLabConsensusGateway;

class IdeaAiConsensusController extends Controller
{
    public function run(RunAiConsensusRequest $request, Idea $idea, IdeaLabConsensusGateway $gateway)
    {
        $template = $this->resolveTemplate($request);

        if ($request->filled('message')) {
            IdeaAiMessage::query()->create([
                'idea_id' => $idea->id,
                'role' => 'user',
                'content' => $request->input('message'),
                'created_by' => auth()->id(),
            ]);
        }

        $run = $gateway->createRun($idea, $template, $request->input('message'), $request->input('mode', 'template'));

        return redirect()
            ->route('idealab.show', $idea)
            ->with('success', $run->status === 'queued'
                ? 'AI Consensus run queued for this idea.'
                : 'AI Consensus payload created. Check the run error if the central service did not queue it.');
    }

    protected function resolveTemplate(RunAiConsensusRequest $request): ?IdeaAiTemplate
    {
        if ($request->filled('template_id')) {
            return IdeaAiTemplate::query()->find($request->integer('template_id'));
        }

        if ($request->filled('template_key')) {
            return $this->ensureTemplate($request->string('template_key')->toString());
        }

        return IdeaAiTemplate::query()
            ->where('key', config('idealab.ai_consensus.default_template_key'))
            ->first();
    }

    protected function ensureTemplate(string $key): ?IdeaAiTemplate
    {
        $templates = [
            'idea_deconstruction' => [
                'name' => 'Idea Deconstruction',
                'entrypoint_type' => 'idea_discussion',
                'description' => 'Discusses, deconstructs and improves an IdeaLab idea before module planning.',
                'system_prompt' => 'You are an LSG product and architecture reviewer. Deconstruct the idea, identify gaps, risks, assumptions and the smallest coherent module path.',
                'user_prompt_template' => "Analyze this idea for WebTools Manager / B.O. Custom LSG.\n\nTitle: {{title}}\nRaw description: {{description_raw}}\nRefined description: {{description_refined}}\n\nReturn valid JSON with: problem, target_users, value, assumptions, risks, open_questions, suggested_refinement, module_candidate, workflow_fit, next_questions.",
                'supports_chat' => true,
                'sort_order' => 1,
            ],
            'project_conversion_brief' => [
                'name' => 'Project Conversion Brief',
                'entrypoint_type' => 'project_conversion',
                'description' => 'Prepares an IdeaLab idea to become a Project Manager project.',
                'system_prompt' => 'You prepare structured project creation payloads for Project Manager. Be precise, phased and implementation-oriented.',
                'user_prompt_template' => "Convert this idea into a Project Manager project proposal.\n\nTitle: {{title}}\nDescription: {{description_refined}}\n\nReturn valid JSON with: project_name, objective, scope, out_of_scope, milestones, tasks, dependencies, risks, mvp_acceptance_criteria, first_implementation_sprint. Milestones and tasks must be ordered and have priority, importance and urgency.",
                'supports_chat' => true,
                'sort_order' => 2,
            ],
            'module_blueprint' => [
                'name' => 'LSG Module Blueprint',
                'entrypoint_type' => 'module_blueprint',
                'description' => 'Creates the first reviewable LSG module blueprint from an idea.',
                'system_prompt' => 'You prepare safe, reviewable module blueprints for WebTools Manager / B.O. Custom LSG. Do not generate or apply executable code.',
                'user_prompt_template' => "Convert this idea into the first version of an LSG module blueprint.\n\nTitle: {{title}}\nRaw description: {{description_raw}}\nRefined description: {{description_refined}}\n\nReturn valid JSON with: module_name, objective, scope, permissions, data_model, migrations, models, controllers, services, routes, views, translations, milestones, tasks, risks, validation_checklist, first_version_plan. Do not generate executable code yet.",
                'supports_chat' => true,
                'sort_order' => 3,
            ],
            'module_refinement' => [
                'name' => 'LSG Module Refinement',
                'entrypoint_type' => 'module_blueprint',
                'description' => 'Refines an LSG module blueprint using Compliance Center findings.',
                'system_prompt' => 'You refine LSG module blueprints based on validation findings. Keep the module in sandbox until every critical issue is resolved.',
                'user_prompt_template' => "Refine this LSG module blueprint using the latest validation issues.\n\nTitle: {{title}}\nRaw description: {{description_raw}}\nRefined description: {{description_refined}}\n\nUser feedback and validation issues are included in the conversation history. Return valid JSON with: changes_required, updated_blueprint, fixes_by_validator, acceptance_criteria, next_validation_plan.",
                'supports_chat' => true,
                'sort_order' => 4,
            ],
        ];

        if (!isset($templates[$key])) {
            return IdeaAiTemplate::query()->where('key', $key)->first();
        }

        return IdeaAiTemplate::query()->updateOrCreate(
            ['key' => $key],
            array_merge($templates[$key], ['is_active' => true])
        );
    }
}
