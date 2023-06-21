<?php

namespace App\Http\Livewire;

use App\Actions\Campfire\ApproveCampfire;
use App\Actions\EmailVerify\GetByToken;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class VerifyEmail extends Component
{
    public function mount($token): \Livewire\Redirector
    {
        $token = GetByToken::make()->handle(token: $token);
        $campfire = $token->campfire;

        if (! $token) {
            abort(403, 'Invalid token');
        }

        \App\Actions\User\ConfirmEmail::run(
            user: $token->user,
            token: $token
        );

        ApproveCampfire::run(campfire: $campfire);

        auth()->login($token->user);

        return redirect()->route('gift', ['campfire' => $token->campfire]);

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.verify-email');
    }
}
