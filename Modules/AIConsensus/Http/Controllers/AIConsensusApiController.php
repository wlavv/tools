<?php

namespace Modules\AIConsensus\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\AIConsensus\Http\Requests\CreateRunRequest;
use Modules\AIConsensus\Services\AIConsensusGateway;

class AIConsensusApiController extends Controller
{
    public function storeRun(CreateRunRequest $request, AIConsensusGateway $gateway): JsonResponse
    {
        $data = $request->validated();
        $data['requested_by'] ??= optional($request->user())->id;

        $run = $gateway->createRun($data);

        return response()->json([
            'data' => $run->load(['template', 'outputs']),
        ], 201);
    }
}
