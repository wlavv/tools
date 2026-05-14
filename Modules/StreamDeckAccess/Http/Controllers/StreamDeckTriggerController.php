<?php

namespace Modules\StreamDeckAccess\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\StreamDeckAccess\Services\StreamDeckAccessService;
use Symfony\Component\HttpFoundation\Response;

class StreamDeckTriggerController extends Controller
{
    public function trigger(Request $request, string $identifier, StreamDeckAccessService $service): Response
    {
        $response = $service->trigger($request, $identifier);

        $response->headers->set('X-Robots-Tag', 'noindex,nofollow,noarchive');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        return $response;
    }
}
