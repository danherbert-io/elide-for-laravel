<?php

declare(strict_types=1);

namespace Elide\Services;

use Elide\Http\Response;
use Elide\View\Partial;
use Illuminate\View\Component;
use Illuminate\View\View;

class Htmx
{
    /** @var array<Partial|View|Component|string> */
    public static array $pendingPartials = [];

    public function __construct(
        public int $number = 0,
    ) {
        $this->number = $number ?: rand();
    }

    public function render(Partial|View|Component|string $component, array $props = []): Response
    {
        return (new Response($component, $props))->usingPartials(function () {
            return [
                ...$this->flushPendingPartials(),
                //@TODO include "always/shared partials"
            ];
        });
    }

    public function sendWithResponse(Partial|View|Component|string $partial): static
    {
        static::$pendingPartials[] = $partial;

        return $this;
    }

    public function partial(View|Component|string $component, array $props = []): Partial {
        return Partial::resolveFrom($component, $props);
    }

    public function flushPendingPartials(): array
    {
        $partials = static::$pendingPartials;
        static::$pendingPartials = [];

        return $partials;
    }
}
