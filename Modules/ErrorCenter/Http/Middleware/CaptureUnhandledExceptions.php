<?php

namespace Modules\ErrorCenter\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\ErrorCenter\Services\ErrorCenterService;
use Modules\ErrorCenter\Support\RequestContextFactory;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CaptureUnhandledExceptions
{
    public function __construct(private readonly ErrorCenterService $errorCenter)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $throwable) {
            if ($this->shouldCapture($request)) {
                try {
                    $this->errorCenter->captureException(
                        $throwable,
                        RequestContextFactory::fromRequest($request, $throwable)
                    );
                } catch (Throwable $captureFailure) {
                    Log::warning('Error Center failed to capture exception.', [
                        'original_exception' => get_class($throwable),
                        'original_message' => $throwable->getMessage(),
                        'capture_exception' => get_class($captureFailure),
                        'capture_message' => $captureFailure->getMessage(),
                    ]);
                }
            }

            throw $throwable;
        }
    }

    private function shouldCapture(Request $request): bool
    {
        if (! config('error-center.enabled', true) || ! config('error-center.capture.enabled', true)) {
            return false;
        }

        foreach ((array) config('error-center.capture.excluded_paths', []) as $pattern) {
            if ($pattern !== '' && Str::is($pattern, $request->path())) {
                return false;
            }
        }

        return true;
    }
}
