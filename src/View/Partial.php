<?php

declare(strict_types=1);

namespace Elide\View;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class Partial
{
    public static function resolveFrom(Partial|View|Component|string $component, array $props = []): Partial
    {
        if ($component instanceof Partial) {
            return $component;
        }

        return new static(
            $component,
            static::resolvePartialName($component),
            $props,
        );
    }

    public static function resolvePartialName(View|Component|string $component): string
    {
        // @TODO - If a class, check for a "ProvidesPartialName" type interface and use that.
        return Str::of(is_string($component) ? $component : $component::class)
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
    ) {
    }

    public function render(): string
    {
        $content = match (true) {
            is_string($this->component) => (string) view($this->component, $this->props),
            $this->component instanceof View => $this->component->with($this->props)->render(),
            $this->component instanceof Component => $this->component->resolveView()
                ->with([...$this->component->data(), ...$this->props])
                ->render(),
        };

        return sprintf(
            '<%1$s id="partial:%2$s" style="display: contents;">%3$s</%1$s>',
            $this->enclosingTagName,
            $this->name,
            $content,
        );
    }
}
