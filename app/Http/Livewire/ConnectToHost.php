<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use App\Models\State;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ConnectToHost extends Component
{
    public Campfire|string $campfire;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $state = '';

    public Collection $states;

    public string $city = '';

    public string $phone = '';

    public string $countryCode = '1';

    public bool $emailUpdates = true;

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
        $this->states = State::all();

        $this->prefillFields();
    }

    public function prefillFields(): void
    {
        if (auth()->check()) {
            $this->firstName = auth()->user()->given_name ?? '';
            $this->lastName = auth()->user()->family_name ?? '';
            $this->email = auth()->user()->email;
            $this->phone = auth()->user()->phone ?? '';
            $this->state = auth()->user()->state ?? '';
            $this->city = auth()->user()->city ?? '';
        }
    }

    public function handle(): void
    {
        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'string|min:7|max:15',
            'state' => 'string|max:255',
            'city' => 'required|string|max:255',
            'emailUpdates' => 'required|boolean',
            'countryCode' => 'required|string|max:3',
        ]);

        $this->campfire->attendees()->create([
            'given_name' => $this->firstName,
            'family_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->countryCode,
            'marketing_updates' => $this->emailUpdates,
        ]);

        $this->emit('closeConnectToHost');

        $this->dispatchBrowserEvent('notify', 'You have connected with '.$this->campfire->host->given_name.'!');
        $this->dispatchBrowserEvent('registered-for-campfire');
        $this->emit('registeredForCampfire');

        $this->resetExcept('campfire', 'states');

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.connect-to-host');
    }
}
