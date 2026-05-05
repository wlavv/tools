<?php

namespace Modules\WebCatalogue\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionLogApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'accepted',
            'message' => 'Session logging table will be enabled in the analytics phase.',
        ], 202);
    }
}
