<?php

declare(strict_types=1);

namespace Elide\Http;

use Elide\Enums\Headers;
use Illuminate\Http\Request;

class HtmxRequest extends Request
{
    /**
     * Whether the request is an HTMX boosted request.
     */
    public function isBoosted(): bool
    {
        return $this->hasHeader(Headers::HTMX_BOOSTED->value);
    }

    /**
     * The current URL of an HTMX AJAX request.
     */
    public function currentUrl(): ?string
    {
        return $this->header(Headers::HTMX_CURRENT_URL->value);
    }

    /**
     * Whether the request is a history restore.
     */
    public function isHistoryRestoreRequest(): bool
    {
        return $this->header(Headers::HTMX_HISTORY_RESTORE_REQUEST->value) === 'true';
    }

    /**
     * Whether the request is from an HTMX prompt.
     */
    public function isPrompt(): bool
    {
        return $this->header(Headers::HTMX_PROMPT->value) === 'true';
    }

    /**
     * Whether the request is an HTMX request.
     */
    public function isHtmxRequest(): bool
    {
        return $this->header(Headers::HTMX_REQUEST->value) === 'true';
    }

    /**
     * The target of the HTMX request.
     */
    public function target(): ?string
    {
        return $this->header(Headers::HTMX_TARGET->value);
    }

    /**
     * The trigger name of the HTMX request.
     */
    public function triggerName(): ?string
    {
        return $this->header(Headers::HTMX_TRIGGER_NAME->value);
    }

    /**
     * The trigger of the HTMX request.
     */
    public function trigger(): ?string
    {
        return $this->header(Headers::HTMX_TRIGGER->value);
    }

    public function partialId(): ?string
    {
        return $this->header(Headers::ELIDE_PARTIAL_ID->value);
    }

    /**
     * The HTMX properties of the request.
     */
    public function inspect(): array
    {
        return [
            'isBoosted' => $this->isBoosted(),
            'currentUrl' => $this->currentUrl(),
            'isHistoryStoreRequest' => $this->isHistoryRestoreRequest(),
            'isPrompt' => $this->isPrompt(),
            'isHtmxRequest' => $this->isHtmxRequest(),
            'target' => $this->target(),
            'triggerName' => $this->triggerName(),
            'trigger' => $this->trigger(),
            'elidePartialId' => $this->partialId(),
        ];
    }
}
