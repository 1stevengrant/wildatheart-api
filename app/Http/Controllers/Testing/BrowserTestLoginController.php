<?php

namespace App\Http\Controllers\Testing;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class BrowserTestLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        // Only allow in local or testing environments
        if (! app()->environment(['local', 'testing'])) {
            abort(404);
        }

        $user = User::findOrFail($request->input('user_id'));

        Auth::login($user);

        return redirect($request->input('redirect', '/'));
    }
}
