<?php

namespace App\Http\Livewire;

use App\Actions\EmailVerify\GetByToken;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class VerifyEmail extends Component
{
    public function mount($token): void
    {
        $token = GetByToken::make()->handle(token: $token);
        ray($token);
        if (! $token) {
            abort(403, 'Invalid token');
        }

        \App\Actions\User\ConfirmEmail::run(
            user: auth()->user(),
            token: $token
        );

        $this->dispatchBrowserEvent('notify', 'Your email has been verified 🥳');

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.verify-email');
    }
}
