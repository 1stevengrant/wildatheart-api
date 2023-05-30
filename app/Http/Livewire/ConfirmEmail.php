<?php

namespace App\Http\Livewire;

use App\Actions\User\SendVerificationEmail;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ConfirmEmail extends Component
{
    public function resendEmail(): void
    {
        SendVerificationEmail::run();

        $this->dispatchBrowserEvent('notify', 'Verification email sent! Check your inbox.');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.confirm-email')
            ->layout('layouts.base');
    }
}
