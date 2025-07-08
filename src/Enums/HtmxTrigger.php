<?php

declare(strict_types=1);

namespace Elide\Enums;

enum HtmxTrigger
{
    case IMMEDIATELY;
    case AFTER_SETTLE;
    case AFTER_SWAP;
}
