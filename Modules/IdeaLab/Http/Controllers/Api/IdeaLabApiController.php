<?php

namespace Modules\IdeaLab\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Modules\IdeaLab\Models\Idea;
use Modules\IdeaLab\Models\IdeaAiTemplate;
use Modules\IdeaLab\Services\AiConsensus\IdeaLabConsensusGateway;

class IdeaLabApiController extends Controller
{
    public function payload(Idea $idea, IdeaLabConsensusGateway $gateway)
    {
        $template = IdeaAiTemplate::query()->where('key', config('idealab.ai_consensus.default_template_key'))->first();

        return response()->json($gateway->buildPayload($idea, $template));
    }
}
