<?php

declare(strict_types=1);

namespace Elide\Contracts;

interface ComponentSpecifiesSwapStrategy
{
    /**
     * Specify an HTMX OOB swap strategy. Used by Partial.
     */
    public function swapStrategy(): string;
}
