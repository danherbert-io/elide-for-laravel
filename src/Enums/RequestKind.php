<?php

declare(strict_types=1);

namespace Elide\Enums;

enum RequestKind: string
{
    case BOTH = 'both'; // Important to note that for a partial to be returned in a non-ajax request it has to be included in the root template.
    case AJAX = 'ajax';
    case NON_AJAX = 'none';
}
