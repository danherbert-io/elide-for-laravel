<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\View;
use Orchestra\Testbench\Concerns\WithWorkbench;

use function Orchestra\Testbench\workbench_path;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        View::addNamespace('test', workbench_path('resources/views/'));

    }
}
