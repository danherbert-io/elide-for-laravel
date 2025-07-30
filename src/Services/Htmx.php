<?php

declare(strict_types=1);

namespace Elide\Services;

use Elide\Enums\RequestKind;
use Elide\Http\HtmxResponse;
use Elide\View\Partial;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use Illuminate\View\View;

class Htmx
{
    /**
     * The root view to be used when returning a full page response.
     */
    protected string $rootView = 'app';

    /**
     * The array of categorised callbacks which provide views, components, or partials to be rendered and returned with
     * the response.
     *
     * @var array<RequestKind, array<int, array<callable():(array<int, Partial|View|Component|string>)>>
     */
    protected array $usingPartials = [
        RequestKind::BOTH->value => [],
        RequestKind::AJAX->value => [],
        RequestKind::NON_AJAX->value => [],
    ];

    /**
     * Partials, views, or components which will be included in the next response.
     *
     * @var array<Partial|View|Component|string>
     */
    public static array $pendingPartials = [];

    /**
     * Create a new Htmx service instance.
     */
    public function __construct(
        public readonly Container $container,
    ) {}

    /**
     * Set the root view to be used for full page responses.
     *
     * @return $this
     */
    public function rootView(string $view): static
    {
        $this->rootView = $view;

        return $this;
    }

    /**
     * Specify a new callable which, when invoked, will return an array of Partials/Views/Components.
     *
     * @param  callable():(array<int, Partial|View|Component|string>)  $callable
     * @return $this
     */
    public function usingPartials(callable $callable, RequestKind $for = RequestKind::BOTH): static
    {
        $this->usingPartials[$for->value][] = $callable;

        return $this;
    }

    /**
     * Create an HtmxResponse to send to the frontend. Automatically determines if the response should include a full
     * page render or just the partials.
     */
    public function render(
        Partial|View|Component|string $component,
        array $props = [],
        ?string $partialName = 'content'
    ): HtmxResponse {
        $response = new HtmxResponse($component, $props, $this->rootView, $partialName);

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

    /**
     * Specify a Partial/View/Component to be returned with the next response.
     *
     * @return $this
     */
    public function sendWithResponse(array|Partial|View|Component|string $partial): static
    {
        $partials = collect(Arr::wrap($partial))
            ->each(function ($candidate) {
                if (is_string($candidate) ||
                    is_subclass_of($candidate, Partial::class) ||
                    is_subclass_of($candidate, View::class) || $candidate instanceof View ||
                    is_subclass_of($candidate, Component::class) || $candidate instanceof Component
                ) {
                    return;
                }

                throw new \InvalidArgumentException('Only Partials, Views, or Components can be returned with HTMX responses');
            })
            ->toArray();

        static::$pendingPartials = array_merge(static::$pendingPartials, $partials);

        return $this;
    }

    /**
     * Create an instance of a Partial from a View/Component.
     */
    public function partial(View|Component|string $component, array $props = [], ?string $name = null): Partial
    {
        return Partial::resolveFrom($component, $props, $name);
    }

    /**
     * Flush and return the current pending partials.
     */
    public function flushPendingPartials(): array
    {
        $partials = static::$pendingPartials;
        static::$pendingPartials = [];

        return $partials;
    }
}
