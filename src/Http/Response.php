<?php

declare(strict_types=1);

namespace Elide\Http;

use Elide\View\Partial;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class Response implements Responsable
{
    protected array $usingPartials = [];

    protected Partial $partial;

    protected HtmxRequest $request;

    public function __construct(
        public readonly Partial|View|Component|string $component,
        public readonly array $props = [],
        public string $rootView = 'app',
    ) {
        $this->partial = Partial::resolveFrom($component, $props);
        $this->request = app(HtmxRequest::class);
    }

    /**
     * {@inheritDoc}
     */
    public function toResponse($request)
    {
        /** @var Collection $partials */
        $partials = collect($this->usingPartials)
            ->map(fn(callable $partial) => $partial())
            ->flatten(1)
            ->map(fn(Partial|View|Component|string $partial) => Partial::resolveFrom($partial))
            ->groupBy(fn(Partial $partial) => $partial->name)
            ->map(fn(Collection $group) => $group->map->render());

        if ($this->request->isHtmxRequest()) {
            return response($partials->flatten()->join("\n"));
        }

        $partials = $partials->map(fn(Collection $group) => $group->join("\n"));

        return \Illuminate\Support\Facades\Response::view($this->rootView, [
            'partials' => $partials->toArray(),
        ]);
    }

    /**
     * @return void
     */
    public function usingPartials(callable $callable): static
    {
        $this->usingPartials[] = $callable;

        return $this;
    }
}
