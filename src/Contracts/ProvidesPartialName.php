<?php

declare(strict_types=1);

namespace Elide\Contracts;

interface ProvidesPartialName
{
    public function partialName(): string;
}
