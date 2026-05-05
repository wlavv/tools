<?php

namespace Modules\WebCatalogue\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\WebCatalogue\Models\Resource;

class AssetApiController extends Controller
{
    public function show(Resource $resource): JsonResponse
    {
        return response()->json(['resource' => $resource]);
    }
}
