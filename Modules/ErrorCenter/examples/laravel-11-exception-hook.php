<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Modules\ErrorCenter\Services\ErrorCenterService;
use Modules\ErrorCenter\Support\RequestContextFactory;

/*
|--------------------------------------------------------------------------
| Optional Laravel 11+ exception hook
|--------------------------------------------------------------------------
|
| The module already captures exceptions using middleware by default. Use this
| only if you disable ERROR_CENTER_AUTO_REGISTER_MIDDLEWARE and prefer to hook
| directly into bootstrap/app.php.
|
| Example usage in bootstrap/app.php:
|
|   ->withExceptions(require base_path('Modules/ErrorCenter/examples/laravel-11-exception-hook.php'))
*/

return function (Exceptions $exceptions): void {
    $exceptions->report(function (Throwable $throwable): void {
        try {
            app(ErrorCenterService::class)->captureException(
                $throwable,
                request() ? RequestContextFactory::fromRequest(request(), $throwable) : []
            );
        } catch (Throwable) {
            // Do not break Laravel's normal exception handling.
        }
    });
};
