<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesAuthenticatedUser;

class MeController extends Controller
{
    use ResolvesAuthenticatedUser;

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser($request->input('email'));

        return response()->json([
            'auth0' => [
                'sub' => $this->getAuth0Sub(),
            ],
            'user' => $user,
        ]);
    }
}
