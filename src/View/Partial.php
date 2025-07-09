<?php

declare(strict_types=1);

namespace Elide\View;

use Elide\Contracts\ComponentSpecifiesSwapStrategy;
use Elide\Contracts\ProvidesPartialName;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class Partial
{
    public static function resolveFrom(
        Partial|View|Component|string $component,
        array $props = [],
        ?string $name = null
    ): Partial {
        if ($component instanceof Partial) {
            return $component;
        }

        return new static(
            $component,
            $name ?: static::resolvePartialName($component),
            $props,
        );
    }

    public static function resolvePartialName(View|Component|string $component): string
    {
        $name = match (true) {
            $component instanceof ProvidesPartialName => $component->partialName(),
            $component instanceof View => $component->name(),
            $component instanceof Component => $component::class,
            default => $component,
        };

        return Str::of($name)
            ->afterLast('\\')
            ->replaceMatches('`[^a-z0-9]+`i', ' ')
            ->snake()
            ->slug()
            ->toString();
    }

    public function __construct(
        public readonly string|Component|View $component,
        public readonly string $name,
        public readonly array $props = [],
        public readonly string $enclosingTagName = 'div',
        public readonly bool|string $swapOob = true,
    ) {}

    public function render(): string
    {
        $component = $this->component;

        if (is_string($component) && is_subclass_of($component, Component::class)) {
            // @TODO - Check into this rather strong assumption of setting props as args...
            //            $component = new $component(...$this->props);
            $component = new $component;
        }

        $content = match (true) {
            is_string($component) && empty($component) => '',
            is_string($component) && ! empty($component) => (string) view($component, $this->props),
            $component instanceof View => $component->with($this->props)->render(),
            $component instanceof Component => $component->resolveView()
                ->with([...$component->data(), ...$this->props])
                ->render(),
        };

        $swapTarget = $component instanceof ComponentSpecifiesSwapStrategy
            ? $component->swapStrategy()
            : (is_bool($this->swapOob) ? ($this->swapOob ? 'true' : 'false') : $this->swapOob);

        return sprintf(
            '<%1$s id="partial:%2$s" style="display: contents;" hx-swap-oob="%4$s">%3$s</%1$s>',
            e($this->enclosingTagName),
            e($this->name),
            $content,
            e($swapTarget),
        );
    }
}
