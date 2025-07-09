<?php

declare(strict_types=1);

namespace Workbench\App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AlternateTestComponent extends Component
{
    public function render(): View
    {
        return view('test::alternate-test-component');
    }
}
