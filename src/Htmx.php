<?php

declare(strict_types=1);

namespace Elide;

use Elide\Http\Response;
use Elide\View\Partial;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * @method static Response render(Partial|View|Component|string $component, array $props = [])
 * @method static Partial partial(View|Component|string $component, array $props = [])
 * @method static Services\Htmx sendWithResponse(Partial|View|Component|string $partial)
 * @method static Partial[] flushPendingPartials()
 */
class Htmx extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Services\Htmx::class;
    }
}
