<?php

namespace App\Http\Controllers\Drupal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Drupal\Concerns\LogsDrupalRedirect;

class DrupalHomeRedirectController extends Controller
{
    use LogsDrupalRedirect;

    public function __invoke(Request $request): RedirectResponse
    {
        $this->logRedirect('drupal', $request->user());

        return to_route('home');
    }
}
