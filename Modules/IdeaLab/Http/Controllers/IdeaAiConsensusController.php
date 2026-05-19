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
        $template = $request->filled('template_id')
            ? IdeaAiTemplate::query()->find($request->integer('template_id'))
            : IdeaAiTemplate::query()->where('key', config('idealab.ai_consensus.default_template_key'))->first();

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
}
