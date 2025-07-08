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
        $this->app->scoped(Htmx::class, function () {
            return new Htmx;
        });

        $this->registerBladeDirectives();
    }

    public function boot(): void
    {
    }

    protected function registerBladeDirectives(): void
    {
        $this->callAfterResolving('blade.compiler', function ($blade) {
            $blade->directive('htmxPartial', [BladeDirective::class, 'partial']);
        });
    }
}
