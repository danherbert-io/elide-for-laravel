<?php

declare(strict_types=1);

namespace Tests\Feature;

use Elide\Htmx;
use Tests\TestCase;

use function Elide\htmx;

class HelperTest extends TestCase
{
    public function test_it_resolves_the_htmx_service(): void
    {
        $this->assertNotNull(htmx());
        $this->assertSame(htmx(), htmx());
        $this->assertSame(htmx(), Htmx::getFacadeRoot());
    }
}
