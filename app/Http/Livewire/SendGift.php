<?php

namespace App\Http\Livewire;

use App\Actions\Address\CreateAddress;
use App\Actions\Campfire\UpdateCampfire;
use App\Mail\Campfire\SendWelcomeEmail;
use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class SendGift extends Component
{
    public Campfire|string $campfire;

    public $country;

    public string $firstName = '';

    public string $lastName = '';

    public string $company = '';

    public string $address1 = '';

    public string $address2 = '';

    public string $city = '';

    public string $state = '';

    public string $zip = '';

    public string $phone = '';

    public bool $submitted = false;

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;

        if ($campfire->hasRequestedGift()) {
            abort(403, 'You have already requested your gift.');
        }

        $campfire->update([
            'approved_at' => now(),
        ]);

        if (! $campfire->welcomeHasBeenSent()) {
            Mail::to($campfire->user)->send(new SendWelcomeEmail($campfire));
        }

        $campfire->update([
            'welcome_sent' => true,
        ]);

        $this->prefillAddress();
    }

    public function prefillAddress(): void
    {
        if (auth()->check()) {
            $this->firstName = auth()->user()->given_name;
            $this->lastName = auth()->user()->family_name;
            $this->company = auth()->user()->company ?? '';
            $this->address1 = $this->campfire->street_address ?? '';
            $this->city = $this->campfire->city ?? '';
            $this->zip = $this->campfire->zip ?? '';
            $this->phone = auth()->user()->phone;
        }
    }

    public function handle(): void
    {
        $this->validate([
            'country' => 'required',
            'firstName' => 'required',
            'lastName' => 'required',
            'company' => 'nullable',
            'address1' => 'required',
            'address2' => 'nullable',
            'city' => 'required',
            'state' => 'nullable',
            'zip' => 'required',
            'phone' => 'nullable',
        ]);

        CreateAddress::run([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'company' => $this->company,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'phone' => $this->phone,
            'is_primary' => true,
            'country' => $this->country,
            'campfire_id' => $this->campfire->id,
        ]);

        UpdateCampfire::run(
            data: ['gift_requested' => true],
            campfire: $this->campfire
        );

        $this->submitted = true;
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.send-gift')
            ->layout('layouts.base');
    }
}
