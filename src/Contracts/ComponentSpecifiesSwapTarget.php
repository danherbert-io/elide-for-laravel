<?php

declare(strict_types=1);


namespace Elide\Contracts;

interface ComponentSpecifiesSwapTarget
{
    public function swapTarget(): string;
}