<?php

declare(strict_types=1);

namespace Elide\Contracts;

use Illuminate\Http\Request;

interface HandlesElideRequests
{
    public function share(Request $request): array;

    public function partials(Request $request): array;

    // @TODO Consider moving this to a service for container cleanup/benefit?
    public static function shareWith(callable $callable): void;

    public static function partialsWith(callable $callable): void;
}
