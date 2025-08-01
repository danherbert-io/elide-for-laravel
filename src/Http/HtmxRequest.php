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
        return $this->hasHeader('HX-Boosted');
    }

    /**
     * The current URL of an HTMX AJAX request.
     */
    public function currentUrl(): ?string
    {
        return $this->header('HX-Current-Url');
    }

    /**
     * Whether the request is a history restore.
     */
    public function isHistoryRestoreRequest(): bool
    {
        return $this->header('HX-History-Restore-Request') === 'true';
    }

    /**
     * Whether the request is from an HTMX prompt.
     */
    public function isPrompt(): bool
    {
        return $this->header('HX-Prompt') === 'true';
    }

    /**
     * Whether the request is an HTMX request.
     */
    public function isHtmxRequest(): bool
    {
        return $this->header('HX-Request') === 'true';
    }

    /**
     * The target of the HTMX request.
     */
    public function target(): ?string
    {
        return $this->header('HX-Target');
    }

    /**
     * The trigger name of the HTMX request.
     */
    public function triggerName(): ?string
    {
        return $this->header('HX-Trigger-Name');
    }

    /**
     * The trigger of the HTMX request.
     */
    public function trigger(): ?string
    {
        return $this->header('HX-Trigger');
    }

    public function partialId(): ?string {
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
