<?php

declare(strict_types=1);

namespace Elide\Providers;

use Elide\Http\HtmxRequest;
use Elide\Services\Htmx;
use Elide\View\BladeDirective;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class ElideServiceProvider extends ServiceProvider
{
    /**
     * Register the app's services and directives.
     */
    public function register(): void
    {
        $this->app->bind(HtmxRequest::class, fn ($container) => HtmxRequest::createFrom($container['request']));
        $this->app->singleton(Htmx::class, fn ($app) => new Htmx($app));
        $this->registerBladeDirectives();
    }

    /**
     * Register the Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        $this->callAfterResolving('blade.compiler', function (BladeCompiler $blade) {
            $blade->directive('htmxPartial', [BladeDirective::class, 'partial']);
        });
    }
}
