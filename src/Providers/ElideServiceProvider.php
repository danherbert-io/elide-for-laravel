<?php

declare(strict_types=1);

namespace Elide\Providers;

use Elide\Contracts\HandlesElideRequests;
use Elide\Services\Htmx;
use Elide\View\BladeDirective;
use Illuminate\Support\ServiceProvider;

class ElideServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/elide.php',
            'elide'
        );

        $this->app->scoped(Htmx::class, function () {
            return new Htmx;
        });

        $this->registerBladeDirectives();
        $this->registerMiddleware();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/elide.php' => config_path('elide.php'),
        ]);
    }

    protected function registerBladeDirectives(): void
    {
        $this->callAfterResolving('blade.compiler', function ($blade) {
            $blade->directive('htmxPartial', [BladeDirective::class, 'partial']);
        });
    }

    protected function registerMiddleware(): void
    {
        $middlewareClass = config('elide.middleware.handles-requests');

        if (! is_subclass_of($middlewareClass, HandlesElideRequests::class)) {
            throw new \InvalidArgumentException('Middleware must implement HandlesElideRequests');
        }

        $this->app['router']->aliasMiddleware('elide', $middlewareClass);
    }
}
