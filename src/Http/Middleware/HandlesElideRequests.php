<?php

declare(strict_types=1);

namespace Elide\Http\Middleware;

use Elide\Contracts\HandlesElideRequests as Contract;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;

class HandlesElideRequests implements Contract
{
    protected static array $includedShares = [];

    protected static array $includedPartials = [];

    public function share(Request $request): array
    {
        $user = $request->user();

        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
            ],
            ...static::$includedShares,
        ];
    }

    public function partials(Request $request): array
    {
        return [
            ...static::$includedPartials,
        ];
    }

    public static function shareWith(callable $callable): void
    {
        static::$includedShares = array_merge(static::$includedShares, $callable());
    }

    public static function partialsWith(callable $callable): void
    {
        static::$includedPartials = array_merge(static::$includedPartials, $callable());
    }
}
