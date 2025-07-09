<?php

declare(strict_types=1);

namespace Elide\Providers;

use Elide\Services\Htmx;
use Elide\View\BladeDirective;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class ElideServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Htmx::class, fn ($app) => new Htmx($app));
        $this->registerBladeDirectives();
    }

    public function boot(): void {}

    protected function registerBladeDirectives(): void
    {
        $this->callAfterResolving('blade.compiler', function (BladeCompiler $blade) {
            $blade->directive('htmxPartial', [BladeDirective::class, 'partial']);
        });
    }
}
