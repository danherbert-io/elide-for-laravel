<?php

declare(strict_types=1);

namespace Feature;

use Elide\Http\HtmxRequest;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HtmxRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test', fn () => '🐱');
    }

    public function test_it_was_boosted(): void
    {
        $request = HtmxRequest::createFrom($this->withHeaders(['HX-Boosted' => 'true'])->get('/test')->baseRequest);
        $this->assertTrue($request->isBoosted());
    }

    public function test_current_url(): void
    {
        $request = HtmxRequest::createFrom($this->withHeaders(['HX-Current-Url' => 'the-current-url'])->get('/test')->baseRequest);
        $this->assertSame($request->currentUrl(), 'the-current-url');
    }

    public function test_is_history_store_request(): void
    {
        $request = HtmxRequest::createFrom($this->withHeaders(['HX-History-Restore-Request' => 'true'])->get('/test')->baseRequest);
        $this->assertTrue($request->isHistoryStoreRequest());
    }

    public function test_is_prompt(): void
    {
        $request = HtmxRequest::createFrom($this->withHeaders(['HX-Prompt' => 'true'])->get('/test')->baseRequest);
        $this->assertTrue($request->isPrompt());
    }

    public function test_is_htmx_request(): void
    {
        $request = HtmxRequest::createFrom($this->withHeaders(['HX-Request' => 'true'])->get('/test')->baseRequest);
        $this->assertTrue($request->isHtmxRequest());
    }

    public function test_has_target(): void
    {
        $request = HtmxRequest::createFrom($this->withHeaders(['HX-Target' => '#the-target'])->get('/test')->baseRequest);
        $this->assertSame($request->target(), '#the-target');
    }

    public function test_has_trigger_name(): void
    {
        $request = HtmxRequest::createFrom($this->withHeaders(['HX-Trigger-Name' => '#the-trigger'])->get('/test')->baseRequest);
        $this->assertSame($request->triggerName(), '#the-trigger');
    }

    public function test_has_trigger(): void
    {
        $request = HtmxRequest::createFrom($this->withHeaders(['HX-Trigger' => '#the-trigger'])->get('/test')->baseRequest);
        $this->assertSame($request->trigger(), '#the-trigger');
    }
}
