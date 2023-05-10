<?php

namespace App\Http\Livewire;

use App\Models\Attendee;
use App\Models\Fire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class RegisterForFire extends Modal
{
    public Fire|int $fire;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $confirmEmail = '';

    public string $phone = '';

    public function mount(Fire $fire): void
    {
        $this->fire = $fire;
    }

    public function handle(): void
    {
        $this->validate([
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'confirmEmail' => 'required|email|same:email',
            'phone' => 'nullable',
        ]);

        Attendee::create([
            'fire_id' => $this->fire->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->close();

        $this->dispatchBrowserEvent('notify', 'You have registered 🥳');

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.register-for-fire');
    }
}
