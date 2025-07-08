<?php

declare(strict_types=1);

namespace Elide\View;

const HTMX_PARTIAL_TEMPLATE = <<<'PHP'
<?php
$key = %s;

if (!is_string($key)) {
    throw new InvalidArgumentException('Key should be a string');
}

if (array_key_exists($key, $partials)) {
    echo $partials[$key];
}
elseif (!class_exists($key) || is_subclass_of($key, Illuminate\View\View::class) || is_subclass_of($key, \Illuminate\View\Component::class)) {
    $name = \Elide\View\Partial::resolvePartialName($key);
    if (array_key_exists($name, $partials)) {
        echo $partials[$name];
    }
}
?>
PHP;

class BladeDirective
{
    public static function partial(string $expression): string
    {
        return sprintf(HTMX_PARTIAL_TEMPLATE, $expression);
    }
}
