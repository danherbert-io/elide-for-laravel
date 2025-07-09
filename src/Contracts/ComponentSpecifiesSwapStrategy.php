<?php

declare(strict_types=1);

namespace Elide\Contracts;

interface ComponentSpecifiesSwapStrategy
{
    public function swapStrategy(): string;
}
