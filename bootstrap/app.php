<?php

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
| Front controllers (public/index.php, index.php) define DOMAIN_POINTED_DIRECTORY
| before requiring this file. Console contexts (artisan, queue workers, tests)
| bypass those front controllers entirely, so fall back to 'public' here to
| avoid "Undefined constant DOMAIN_POINTED_DIRECTORY" errors outside HTTP requests.
*/
if (!defined('DOMAIN_POINTED_DIRECTORY')) {
    define('DOMAIN_POINTED_DIRECTORY', 'public');
}

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

/*
| CORS is handled per-route-group by \Illuminate\Http\Middleware\HandleCors
| (registered on the `api` middleware group) using config/cors.php. Do not
| re-add global Access-Control-Allow-* headers here: they would apply to
| every response, including the admin panel, and bypass the CORS config.
*/

return $app;
