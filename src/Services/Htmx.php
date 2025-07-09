<?php

declare(strict_types=1);

namespace Elide\Services;

use Elide\Http\Response;
use Elide\View\Partial;
use Illuminate\Container\Container;
use Illuminate\View\Component;
use Illuminate\View\View;

class Htmx
{
    protected string $rootView = 'app';

    protected array $usingPartials = [];

    /** @var array<Partial|View|Component|string> */
    public static array $pendingPartials = [];

    public function __construct(
        public readonly Container $container,
    ) {}

    public function rootView(string $view): static
    {
        $this->rootView = $view;

        return $this;
    }

    public function usingPartials(callable $callable): static
    {
        $this->usingPartials[] = $callable;

        return $this;
    }

    public function render(
        Partial|View|Component|string $component,
        array $props = [],
        ?string $partialName = 'content'
    ): Response {
        $response = new Response($component, $props, $this->rootView, $partialName);

        foreach ($this->usingPartials as $callable) {
            $response->usingPartials($callable);
        }

        return $response
            ->reswap('none')
            ->usingPartials(function () {
                return $this->flushPendingPartials();
            });
    }

    public function sendWithResponse(Partial|View|Component|string $partial): static
    {
        static::$pendingPartials[] = $partial;

        return $this;
    }

    public function partial(View|Component|string $component, array $props = [], ?string $name = null): Partial
    {
        return Partial::resolveFrom($component, $props, $name);
    }

    public function flushPendingPartials(): array
    {
        $partials = static::$pendingPartials;
        static::$pendingPartials = [];

        return $partials;
    }
}
