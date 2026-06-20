<?php

/*
|--------------------------------------------------------------------------
| Prevent Shared Apache Environment Bleed
|--------------------------------------------------------------------------
|
| XAMPP/mod_php can keep process-level environment variables between
| different local Laravel projects. Clear project-specific keys before
| Laravel loads this application's .env file so another vhost cannot make
| Webtools use the wrong database or session cookie.
|
*/

foreach ([
    'APP_NAME',
    'APP_ENV',
    'APP_KEY',
    'APP_DEBUG',
    'APP_URL',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'CACHE_DRIVER',
    'QUEUE_CONNECTION',
    'SESSION_DRIVER',
    'SESSION_LIFETIME',
    'SESSION_DOMAIN',
    'SESSION_COOKIE',
    'REDIS_HOST',
    'REDIS_PASSWORD',
    'REDIS_PORT',
] as $environmentKey) {
    unset($_ENV[$environmentKey], $_SERVER[$environmentKey]);
    putenv($environmentKey);
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
