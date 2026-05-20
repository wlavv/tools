<?php

namespace Modules\IdeaLab\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\IdeaLab\Models\Idea;
use Modules\IdeaLab\Services\AiConsensus\IdeaLabConsensusGateway;
use Modules\IdeaLab\Services\IdeaToolWorkflowService;

class IdeaWorkflowController extends Controller
{
    public function index(IdeaToolWorkflowService $workflow)
    {
        $this->setPageTitle('IdeaLab Tool Workflow');

        $ideas = Idea::query()
            ->with(['category', 'aiConsensusRuns.aiConsensusRun'])
            ->latest()
            ->paginate(25);

        $snapshots = $ideas->getCollection()
            ->mapWithKeys(fn (Idea $idea) => [$idea->id => $workflow->snapshot($idea)])
            ->all();

        return $this->view('idealab::ideas.workflow-index', compact('ideas', 'snapshots'));
    }

    public function requestDiscussion(Idea $idea, IdeaLabConsensusGateway $gateway): RedirectResponse
    {
        $gateway->createRun($idea, $this->ensureDiscussionTemplate(), null, 'template');

        return back()->with('success', 'AI Consensus discussion run created.');
    }

    public function requestBlueprint(Idea $idea, IdeaLabConsensusGateway $gateway): RedirectResponse
    {
        $template = $this->ensureBlueprintTemplate();
        $gateway->createRun($idea, $template, null, 'template');

        return back()->with('success', 'LSG module blueprint run created.');
    }

    public function requestReformulation(Idea $idea, IdeaToolWorkflowService $workflow, IdeaLabConsensusGateway $gateway): RedirectResponse
    {
        $template = $this->ensureBlueprintTemplate();
        $gateway->createRun($idea, $template, $workflow->feedbackPrompt($idea), 'chat');

        return back()->with('success', 'Reformulation request sent to AI Consensus with validation issues.');
    }

    public function createSandbox(Idea $idea, IdeaToolWorkflowService $workflow): RedirectResponse
    {
        $sandbox = $workflow->createSandboxModule($idea);

        return back()->with('success', 'Sandbox module created: ' . $sandbox['module_name']);
    }

    public function runCompliance(Idea $idea, IdeaToolWorkflowService $workflow): RedirectResponse
    {
        $run = $workflow->runCompliance($idea);

        if (!$run) {
            return back()->with('error', 'Sandbox module missing or Compliance Center unavailable.');
        }

        return back()->with('success', 'Compliance run completed: ' . $run->final_status . ' / ' . $run->final_score);
    }

    public function approveGoLive(Idea $idea, IdeaToolWorkflowService $workflow): RedirectResponse
    {
        $snapshot = $workflow->snapshot($idea);
        if (!data_get($snapshot, 'compliance.run_id') || !empty($snapshot['issues'])) {
            return back()->with('error', 'Go live approval requires a clean Compliance Center run.');
        }

        $workflow->approveGoLive($idea);

        return back()->with('success', 'Idea approved for go live.');
    }

    public function requestChanges(Request $request, Idea $idea, IdeaToolWorkflowService $workflow): RedirectResponse
    {
        $workflow->requestChanges($idea, $request->input('reason'));

        return back()->with('success', 'Changes requested and added to the next AI Consensus reformulation prompt.');
    }

    protected function ensureBlueprintTemplate(): ?\Modules\IdeaLab\Models\IdeaAiTemplate
    {
        return \Modules\IdeaLab\Models\IdeaAiTemplate::query()->updateOrCreate(
            ['key' => 'module_blueprint'],
            [
                'name' => 'LSG Module Blueprint',
                'entrypoint_type' => 'module_blueprint',
                'description' => 'Creates a reviewable LSG module blueprint from an IdeaLab idea.',
                'system_prompt' => 'You prepare safe, reviewable module blueprints for WebTools Manager / B.O. Custom LSG. Do not generate or apply executable code.',
                'user_prompt_template' => "Convert this idea into an LSG module blueprint.\n\nTitle: {{title}}\nRaw description: {{description_raw}}\nRefined description: {{description_refined}}\n\nReturn valid JSON with: module_name, objective, scope, permissions, data_model, migrations, models, controllers, services, routes, views, translations, milestones, tasks, risks, validation_checklist, first_version_plan.",
                'supports_chat' => true,
                'sort_order' => 3,
                'is_active' => true,
            ]
        );
    }

    protected function ensureDiscussionTemplate(): ?\Modules\IdeaLab\Models\IdeaAiTemplate
    {
        return \Modules\IdeaLab\Models\IdeaAiTemplate::query()->updateOrCreate(
            ['key' => 'idea_deconstruction'],
            [
                'name' => 'Idea Deconstruction',
                'entrypoint_type' => 'idea_discussion',
                'description' => 'Discusses, deconstructs and improves an IdeaLab idea before module planning.',
                'system_prompt' => 'You are an LSG product and architecture reviewer. Deconstruct the idea, identify gaps, risks, assumptions and the smallest coherent module path.',
                'user_prompt_template' => "Analyze this idea for WebTools Manager / B.O. Custom LSG.\n\nTitle: {{title}}\nRaw description: {{description_raw}}\nRefined description: {{description_refined}}\n\nReturn valid JSON with: problem, target_users, value, assumptions, risks, open_questions, suggested_refinement, module_candidate, workflow_fit, next_questions.",
                'supports_chat' => true,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }
}
