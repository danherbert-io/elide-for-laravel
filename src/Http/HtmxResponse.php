<?php

declare(strict_types=1);

namespace Elide\Http;

use Elide\Enums\HtmxTriggerTiming;
use Elide\View\Partial;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\ResponseTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View as IlluminateView;
use Illuminate\View\Component;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class HtmxResponse implements Responsable
{
    use ResponseTrait;

    /**
     * An array of callables which will return an array of Partials.
     *
     * @var array<callable():(array<int, Partial|View|Component|string>)>
     */
    protected array $usingPartials = [];

    /**
     * An array of callables which will filter the Partials which will be returned with the response.
     *
     * @var array<callable(string, string):(bool)>
     */
    protected array $filteringPartials = [];

    /**
     * The main partial to be rendered with this response.
     */
    protected ?Partial $partial = null;

    /**
     * The HtmxRequest which the response will use to determine its responding content.
     *
     * @var HtmxRequest|\Illuminate\Http\Request
     */
    public readonly HtmxRequest $request;

    /**
     * Headers to be returned.
     */
    protected ResponseHeaderBag $headers;

    /**
     * Status code of the response.
     */
    protected int $status = SymfonyResponse::HTTP_OK;

    /**
     * Optional title to return to the HTMX frontend.
     */
    protected ?string $title = null;

    /**
     * Whether the response should only return the origin Partial if it was provided. Either is boolean or an array
     * of accepted partial IDs.
     */
    protected bool|array $scopeToRequestingPartial = false;

    /**
     * Whether the HTMX response should omit individual partials which have been rendered as part of another partial.
     */
    protected bool $omitRenderedChildPartials = false;

    /**
     * Instantiate a new HTMX Response.
     */
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
        $this->request = app(HtmxRequest::class);
        $this->status($status);
    }

    /**
     * Set the status code.
     *
     * @return $this
     */
    public function status(int $code): static
    {
        if (! array_key_exists($code, SymfonyResponse::$statusTexts)) {
            throw new \InvalidArgumentException(sprintf(
                'Provided code "%s" is not a valid HTTP status code.',
                $code,
            ));
        }

        $this->status = $code;

        return $this;
    }

    /**
     * Create an HTTP response for HTMX. If the request was an HTMX AJAX request, only partials will be returned. If it
     * was not an HTMX AJAX request, a full render of the main partial will be returned. If a title has been specified
     * that will be injected into the response content.
     */
    public function toResponse($request)
    {
        $sharedProps = ['partials' => []];

        /** @var Collection $partials */
        $partials =
            collect($this->usingPartials)
                ->map(fn (callable $partial) => $partial())
                ->flatten(1)
                ->map(fn (Partial|View|Component|string $partial) => Partial::resolveFrom($partial))
                ->when($this->component, function (Collection $collection) {
                    $collection->push($this->partial);
                })
                ->groupBy(fn (Partial $partial) => $partial->name)
                ->map(function (Collection $group, string $key) use (&$sharedProps) {
                    $renderedGroup = $group->map->render()->join("\n");
                    $sharedProps['partials'][$key] = $renderedGroup;

                    // Progressively share rendered partials for upcoming components/partials to be rendered.
                    IlluminateView::share($sharedProps);

                    return $renderedGroup;
                });

        if ($this->request->isHtmxRequest()) {
            // @TODO Consider if we can optimise how we isolate these islands. Nested partials necessitate that we
            //       need to still render all partials, even though we're scoping down to a single one for the
            //       response.
            if ($this->scopeToRequestingPartial) {
                $partialId = $this->request->partialId();

                if ($partials->has($partialId)) {
                    $scopeCandidates = is_array($this->scopeToRequestingPartial)
                        ? $this->scopeToRequestingPartial
                        : [$partialId];

                    if (in_array($partialId, $scopeCandidates)) {
                        $this->filteringPartials(fn ($_, $id) => $id === $partialId);
                    }
                }
            }

            if ($this->omitRenderedChildPartials) {
                $this->buildOmitRenderedChildPartialsFilter($partials);
            }

            if (count($this->filteringPartials)) {
                foreach ($this->filteringPartials as $filter) {
                    $partials = $partials->filter($filter);
                }
            }

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

        if (! $this->component) {
            return response(
                content: null,
                status: $this->status,
                headers: $this->headers->all(),
            );
        }

        if ($this->title) {
            $sharedProps['title'] = $this->title;
        }

        IlluminateView::share($sharedProps);

        return Response::view(
            $this->rootView,
            $sharedProps,
            status: $this->status,
            headers: $this->headers->all(),
        );
    }

    /**
     * Set the title to be sent with the response.
     *
     * @return $this
     */
    public function title(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Specify a callable which will be used to provide partials to the response when it is rendered.
     *
     * @param  callable():(array<int, Partial|View|Component|string>)  $callable
     * @return $this
     */
    public function usingPartials(callable $callable): static
    {
        $this->usingPartials[] = $callable;

        return $this;
    }

    /**
     * Specify a callable which will be used to filter the partials which will be returned with the response.
     * The first string argument is HTML content, the second is the ID.
     *
     * @param  callable(string, string):(bool)  $callable
     * @return $this
     */
    public function filteringPartials(callable $callable): static
    {
        $this->filteringPartials[] = $callable;

        return $this;
    }

    /**
     * Specify a location for HTMX to navigate to.
     *
     * @see https://htmx.org/headers/hx-location/
     */
    public function location(string $path, ?string $target = null): static
    {
        if (is_null($target)) {
            $this->headers->set('HX-Location', $path);
        } else {
            $this->headers->set('HX-Location', json_encode(compact('path', 'target')));
        }

        return $this;
    }

    /**
     * Specify a URL for HTMX to push.
     *
     * @see https://htmx.org/headers/hx-push-url/
     */
    public function pushUrl(false|string $url): static
    {
        $this->headers->set('HX-Push-Url', $url === false ? 'false' : $url);

        return $this;
    }

    /**
     * Specify a URL for HTMX to perform a full redirect to.
     *
     * @see https://htmx.org/headers/hx-redirect/
     */
    public function redirect(string $url): static
    {
        $this->headers->set('HX-Redirect', $url);

        return $this;
    }

    /**
     * Specify to HTMX that the page should be refreshed.
     *
     * @see https://htmx.org/headers/hx-refresh/
     */
    public function refresh(): static
    {
        $this->headers->set('HX-Refresh', 'true');

        return $this;
    }

    /**
     * Instruct HTMX to replace the URL with the provided URL.
     *
     * @see https://htmx.org/headers/hx-replace-url/
     */
    public function replaceUrl(false|string $url): static
    {
        $this->headers->set('HX-Replace-Url', $url === false ? 'false' : $url);

        return $this;
    }

    /**
     * Instruct HTMX with a new swap target.
     *
     * @see https://htmx.org/docs/#response-headers
     */
    public function reswap(string $swap): static
    {
        $this->headers->set('HX-Reswap', $swap);

        return $this;
    }

    /**
     * Retarget HTMX.
     *
     * @see https://htmx.org/docs/#response-headers
     */
    public function retarget(string $cssSelector): static
    {
        $this->headers->set('HX-Retarget', $cssSelector);

        return $this;
    }

    /**
     * Instruct HTMX to reselect.
     *
     * @see https://htmx.org/docs/#response-headers
     */
    public function reselect(string $cssSelector): static
    {
        $this->headers->set('HX-Reselect', $cssSelector);

        return $this;
    }

    /**
     * Send a trigger to HTMX.
     *
     * @see https://htmx.org/headers/hx-trigger/
     */
    public function trigger(string|array $event, HtmxTriggerTiming $when = HtmxTriggerTiming::IMMEDIATELY): static
    {
        $this->headers->set(
            $when->header(),
            is_string($event) ? $event : json_encode($event),
        );

        return $this;
    }

    /**
     * Specify if the response should be scoped to the requesting partial, if the request is a HTMX request and an
     * originating Partial was specified. If no matching partial is provided, the full set will be returned.
     */
    public function scopeToRequestingPartial(bool|array $shouldScope = true): static
    {
        if (is_array($shouldScope)) {
            $shouldScope = collect($shouldScope)
                ->map(fn ($item) => is_string($item) ? trim($item) : null)
                ->filter()
                ->toArray();

            if (! count($shouldScope)) {
                $shouldScope = false;
            }
        }

        $this->scopeToRequestingPartial = $shouldScope;

        return $this;
    }

    /**
     * Specify if the response should omit any partials which have been rendered as part of another partial.
     */
    public function omitRenderedChildPartials(bool $shouldOmit = true): static
    {
        $this->omitRenderedChildPartials = $shouldOmit;

        return $this;
    }

    /**
     * Disable scoping to the requested partial.
     */
    public function doNotScopeToRequestingPartial(): static
    {
        $this->scopeToRequestingPartial(false);

        return $this;
    }

    private function buildOmitRenderedChildPartialsFilter(Collection $partials): void
    {
        $partialsCount = $partials->count();
        $partialKeys = $partials->keys();

        $this->filteringPartials(function ($_, $id) use ($partials, $partialKeys, $partialsCount) {
            $rendered = false;

            for ($i = 0; $i < $partialsCount; $i++) {
                $key = $partialKeys[$i];
                if ($key === $id) {
                    continue;
                }

                $partialContent = $partials->get($key);

                if (str_contains($partialContent, sprintf('id="partial:%s"', $id))) {
                    $rendered = true;
                    break;
                }
            }

            return ! $rendered;
        });
    }
}
