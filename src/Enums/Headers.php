<?php

declare(strict_types=1);


namespace Elide\Enums;

enum Headers: string
{
    case ELIDE_PARTIAL_ID = 'X-Elide-Partial-Id';
}
