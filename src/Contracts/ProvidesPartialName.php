<?php

declare(strict_types=1);

namespace Elide\Contracts;

interface ProvidesPartialName
{
    /**
     * Specify a name to be used when wrapped by a Partial.
     */
    public function partialName(): string;
}
