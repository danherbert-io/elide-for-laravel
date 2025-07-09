<?php

declare(strict_types=1);

namespace Elide\Enums;

enum HtmxTrigger
{
    case IMMEDIATELY;
    case AFTER_SETTLE;
    case AFTER_SWAP;

    public function header(): string
    {
        return match ($this) {
            HtmxTrigger::IMMEDIATELY => 'HX-Trigger',
            HtmxTrigger::AFTER_SETTLE => 'HX-Trigger-After-Settle',
            HtmxTrigger::AFTER_SWAP => 'HX-Trigger-After-Swap',
        };
    }
}
