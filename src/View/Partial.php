<?php

declare(strict_types=1);

namespace Elide\View;

use Elide\Contracts\ComponentSpecifiesSwapStrategy;
use Elide\Contracts\ProvidesPartialName;
use Elide\Http\HtmxRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class Partial
{
    /**
     * Resolve a partial for the provided Partial/View/Component.
     */
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

    /**
     * Given a View/Component/string, resolve a name suitable for a Partial.
     */
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

    /**
     * Instantiate a new Partial for the provided View/Component and name. Optionally specify what the containing tag
     * name or HTMX OOB swap strategy should be.
     */
    public function __construct(
        public readonly string|Component|View $component,
        public readonly string $name,
        public readonly array $props = [],
        public readonly string $enclosingTagName = 'div',
        public readonly bool|string $swapOob = true,
    ) {}

    /**
     * Render the partial.
     *
     * @throws \Throwable
     */
    public function render(): string
    {
        $component = $this->component;

        if (is_string($component) && is_subclass_of($component, Component::class)) {
            $component = app($component, $this->props);
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

        // Don't make a request for _every_ partial....
        $isHtmxRequest = Cache::driver('array')->rememberForever(
            'is-htmx-request',
            function () {
                return app(HtmxRequest::class)->isHtmxRequest();
            }
        );

        return sprintf(
            '<%1$s id="partial:%2$s" style="display: contents;"%4$s>%3$s</%1$s>',
            e($this->enclosingTagName),
            e($this->name),
            $content,
            $isHtmxRequest ? sprintf(' hx-swap-oob="%s"', e($swapTarget)) : '',
        );
    }
}
