<?php

declare(strict_types=1);

namespace Elide;

use Elide\Services\Htmx;
use Illuminate\Container\Container;

function htmx(): Htmx
{
    return Container::getInstance()->make(Htmx::class);
}
