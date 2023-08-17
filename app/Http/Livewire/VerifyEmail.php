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

        if (! $token) {
            abort(403, 'That token is invalid or has already been used.');
        }

        $campfire = $token->campfire;

        \App\Actions\User\ConfirmEmail::run(
            user: $token->user,
            token: $token
        );

        ApproveCampfire::run(campfire: $campfire);

        auth()->login($token->user);

        return to_route('gift', ['campfire' => $token->campfire]);

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.verify-email');
    }
}
