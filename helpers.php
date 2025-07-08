<?php

declare(strict_types=1);

namespace Elide;

use Elide\Services\Htmx;

function htmx(): Htmx
{
    return app(Htmx::class);
}
