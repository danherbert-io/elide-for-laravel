<?php

declare(strict_types=1);

namespace Elide\Enums;

enum HtmxTriggerTiming
{
    case IMMEDIATELY;
    case AFTER_SETTLE;
    case AFTER_SWAP;

    /**
     * The HTTP header name for the timing.
     */
    public function header(): string
    {
        return match ($this) {
            HtmxTriggerTiming::IMMEDIATELY => 'HX-Trigger',
            HtmxTriggerTiming::AFTER_SETTLE => 'HX-Trigger-After-Settle',
            HtmxTriggerTiming::AFTER_SWAP => 'HX-Trigger-After-Swap',
        };
    }
}
