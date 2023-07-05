<?php

namespace App\Http\Livewire;

use App\Jobs\PostToZapier;
use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UpdateAddress extends Component
{
    public string|Campfire $campfire;

    public string $street_address = '';

    public ?string $address2 = null;

    public ?string $company = null;

    public bool $addressUpdated = false;

    public function mount(Campfire $campfire)
    {
        $this->campfire = $campfire;

        if ($this->campfire->hasAddress() && $this->campfire->address->street_address !== null) {
            return to_route('home');
        }
    }

    public function handle(): void
    {
        $validated = $this->validate([
            'street_address' => 'required|max:255|min:10',
            'address2' => 'nullable|max:255|min:2',
            'company' => 'nullable|max:255|min:2',
        ]);

        $this->campfire->address->update($validated);

        PostToZapier::dispatch(data: [
            'email' => $this->campfire->host->email,
            'first_name' => $this->campfire->host->given_name,
            'last_name' => $this->campfire->host->family_name,
            'address_1' => $this->street_address,
            'address_2' => $this->address2,
            'city' => $this->campfire->city,
            'state' => $this->campfire->state,
            'zip' => $this->campfire->zip,
            'country' => $this->campfire->country,
        ]);

        $this->addressUpdated = true;

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.update-address')->layout('layouts.empty');
    }
}
