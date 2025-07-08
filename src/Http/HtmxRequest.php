<?php

declare(strict_types=1);

namespace Elide\Http;

use Illuminate\Http\Request;

class HtmxRequest extends Request
{
    public function isBoosted(): bool
    {
        return $this->hasHeader('HX-Boosted');
    }

    public function currentUrl(): string
    {
        return $this->header('HX-Current-Url');
    }

    public function isHistoryStoreRequest(): bool
    {
        return $this->header('HX-History-Restore-Request') === 'true';
    }

    public function isPrompt(): bool
    {
        return $this->header('HX-Prompt') === 'true';
    }

    public function isHtmxRequest(): bool
    {
        return $this->header('HX-Request') === 'true';
    }

    public function target(): ?string
    {
        return $this->header('HX-Target');
    }

    public function triggerName(): ?string
    {
        return $this->header('HX-Trigger-Name');
    }

    public function trigger(): ?string
    {
        return $this->header('HX-Trigger');
    }
}
