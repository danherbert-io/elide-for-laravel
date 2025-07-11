<?php

declare(strict_types=1);

namespace Elide;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Elide\Services\Htmx rootView(string $view)
 * @method static \Elide\Services\Htmx usingPartials(callable $callable, \Elide\Enums\RequestKind $for = 'both')
 * @method static \Elide\Http\Response render(\Elide\View\Partial|\Illuminate\View\View|\Illuminate\View\Component|string $component, array $props = [], string|null $partialName = 'content')
 * @method static \Elide\Services\Htmx sendWithResponse(\Elide\View\Partial|\Illuminate\View\View|\Illuminate\View\Component|string $partial)
 * @method static \Elide\View\Partial partial(\Illuminate\View\View|\Illuminate\View\Component|string $component, array $props = [], string|null $name = null)
 * @method static array flushPendingPartials()
 *
 * @see \Elide\Services\Htmx
 */
class Htmx extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Services\Htmx::class;
    }
}
