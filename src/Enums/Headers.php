<?php

declare(strict_types=1);

namespace Elide\Enums;

enum Headers: string
{
    case ELIDE_PARTIAL_ID = 'X-Elide-Partial-Id';

    case HTMX_BOOSTED = 'HX-Boosted';
    case HTMX_CURRENT_URL = 'HX-Current-Url';
    case HTMX_HISTORY_RESTORE_REQUEST = 'HX-History-Restore-Request';
    case HTMX_PROMPT = 'HX-Prompt';
    case HTMX_REQUEST = 'HX-Request';
    case HTMX_TARGET = 'HX-Target';
    case HTMX_TRIGGER_NAME = 'HX-Trigger-Name';
    case HTMX_TRIGGER = 'HX-Trigger';
}
