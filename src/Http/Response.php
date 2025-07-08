<?php

declare(strict_types=1);

namespace Elide\Http;

use Elide\Enums\HtmxTrigger;
use Elide\View\Partial;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\ResponseTrait;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Response implements Responsable
{
    use ResponseTrait;

    protected array $usingPartials = [];

    protected null|Partial $partial = null;

    protected HtmxRequest $request;

    protected ResponseHeaderBag $headers;

    protected int $status = SymfonyResponse::HTTP_OK;

    protected ?string $title = null;

    public function __construct(
        public readonly null|Partial|View|Component|string $component = null,
        public readonly array $props = [],
        public readonly string $rootView = 'app',
        public readonly ?string $partialName = null,
        public readonly bool $sourceElementSwap = false,
        int $status = SymfonyResponse::HTTP_OK,
        array $headers = [],
    ) {
        $this->headers = new ResponseHeaderBag($headers);
        if ($this->component) {
            $this->partial = Partial::resolveFrom($component, $props, $this->partialName);
        }
        $this->request = HtmxRequest::capture();
        $this->status($status);
    }

    public function status(int $code): static
    {
        if (!array_key_exists($code, SymfonyResponse::$statusTexts)) {
            throw new \InvalidArgumentException(sprintf(
                'Provided code "%s" is not a valid HTTP status code.',
                $code,
            ));
        }

        $this->status = $code;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function toResponse($request)
    {
        /** @var Collection $partials */
        $partials = $this->component
            ? collect($this->usingPartials)
                ->map(fn(callable $partial) => $partial())
                ->flatten(1)
                ->map(fn(Partial|View|Component|string $partial) => Partial::resolveFrom($partial))
                ->push($this->partial)
                ->groupBy(fn(Partial $partial) => $partial->name)
                ->map(fn(Collection $group) => $group->map->render())
            : collect();

        if ($this->request->isHtmxRequest()) {
            return response(
                content: $partials
                    ->when($this->title, function (Collection $collection) {
                        return $collection->prepend(sprintf(
                            '<title>%s</title>',
                            e($this->title),
                        ));
                    })
                    ->flatten()
                    ->join("\n"),
                status: $this->status,
                headers: $this->headers->all(),
            );
        }

        if (!$this->component) {
            return response(
                content: null,
                status: $this->status,
                headers: $this->headers->all(),
            );
        }

        $partials = $partials->map(fn(Collection $group) => $group->join("\n"));

        $props = ['partials' => $partials->toArray()];

        if ($this->title) {
            $props['title'] = $this->title;
        }

        return \Illuminate\Support\Facades\Response::view(
            $this->rootView,
            $props,
            status: $this->status,
            headers: $this->headers->all(),
        );
    }

    public function title(null|string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function usingPartials(callable $callable): static
    {
        $this->usingPartials[] = $callable;

        return $this;
    }

    /**
     * @return $this
     */
    public function location(string $path, ?string $target = null): static
    {
        if (is_null($target)) {
            $this->headers->set('HX-Location', $path);
        } else {
            $this->headers->set('Location', json_encode(compact('path', 'target')));
        }

        return $this;
    }

    public function pushUrl(false|string $url): static
    {
        $this->headers->set('HX-Push-Url', $url === false ? 'false' : $url);

        return $this;
    }

    /**
     * client full redirect
     *
     * @return $this
     */
    public function redirect(string $url): static
    {
        $this->headers->set('HX-Redirect', $url);

        return $this;
    }

    /**
     * client refresh
     *
     * @return $this
     */
    public function refresh(): static
    {
        $this->headers->set('HX-Refresh', 'true');

        return $this;
    }

    public function replaceUrl(false|string $url): static
    {
        $this->headers->set('HX-Replace-Url', $url === false ? 'false' : $url);

        return $this;
    }

    public function reswap(string $swap): static
    {
        $this->headers->set('HX-Reswap', $swap);

        return $this;
    }

    public function retarget(string $cssSelector): static
    {
        $this->headers->set('HX-Retarget', $cssSelector);

        return $this;
    }

    public function reselect(string $cssSelector): static
    {
        $this->headers->set('HX-Retarget', $cssSelector);

        return $this;
    }

    /**
     * @return $this
     *
     * @see https://htmx.org/headers/hx-trigger/
     */
    public function trigger(string|array $event, HtmxTrigger $when = HtmxTrigger::IMMEDIATELY): static
    {
        $header = match ($when) {
            HtmxTrigger::IMMEDIATELY => 'HX-Trigger',
            HtmxTrigger::AFTER_SETTLE => 'HX-Trigger-After-Settle',
            HtmxTrigger::AFTER_SWAP => 'HX-Trigger-After-Swap',
        };

        $this->headers->set($header, json_encode($event));

        return $this;
    }
}
