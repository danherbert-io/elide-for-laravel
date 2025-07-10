<?php

declare(strict_types=1);

namespace Elide\Services;

use Elide\Enums\RequestKind;
use Elide\Http\Response;
use Elide\View\Partial;
use Illuminate\Container\Container;
use Illuminate\View\Component;
use Illuminate\View\View;

class Htmx
{
    protected string $rootView = 'app';

    protected array $usingPartials = [
        RequestKind::BOTH->value => [],
        RequestKind::AJAX->value => [],
        RequestKind::NON_AJAX->value => [],
    ];

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

    public function usingPartials(callable $callable, RequestKind $for = RequestKind::BOTH): static
    {
        $this->usingPartials[$for->value][] = $callable;

        return $this;
    }

    public function render(
        Partial|View|Component|string $component,
        array $props = [],
        ?string $partialName = 'content'
    ): Response {
        $response = new Response($component, $props, $this->rootView, $partialName);

        $partials = [
            ...$this->usingPartials[($response->request->isHtmxRequest() ? RequestKind::AJAX->value : RequestKind::NON_AJAX->value)],
            ...$this->usingPartials[RequestKind::BOTH->value],
        ];

        foreach ($partials as $callable) {
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
