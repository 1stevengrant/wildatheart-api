<?php

namespace App\Http\Livewire;

use App\Actions\Address\CreateAddress;
use App\Actions\Campfire\UpdateCampfire;
use App\Jobs\PostToZapier;
use App\Mail\Campfire\GiftRequested;
use App\Models\Campfire;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\RequiredIf;
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

        $this->prefillAddress();

        SEOTools::setTitle('Claim Your Gift 🥳');
    }

    public function prefillAddress(): void
    {
        if (auth()->check()) {
            $this->firstName = auth()->user()->given_name ?? '';
            $this->lastName = auth()->user()->family_name ?? '';
            $this->company = auth()->user()->company ?? '';
            $this->address1 = $this->campfire->street_address ?? '';
            $this->city = $this->campfire->city ?? '';
            $this->state = $this->campfire->state ?? '';
            $this->zip = $this->campfire->zip ?? '';
            $this->phone = auth()->user()->phone ?? '';
            $this->country = $this->campfire->country ?? '';
        }
    }

    public function handle(): void
    {
        $this->validate([
            'country' => 'required|max:255',
            'firstName' => 'required|max:255|min:2',
            'lastName' => 'required|max:255|min:2',
            'company' => 'nullable',
            'address1' => 'required|max:255',
            'address2' => 'nullable',
            'city' => 'required|max:255',
            'state' => [
                'nullable',
                new RequiredIf($this->country === 'US'),
            ],
            'zip' => 'required|max:20|min:4',
            'phone' => 'required|numeric|digits_between:10,15',
        ]);

        CreateAddress::run([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'company' => $this->company,
            'street_address' => $this->address1,
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

        Mail::to($this->campfire->host->email)
            ->send(new GiftRequested($this->campfire));

        PostToZapier::dispatch(data: [
            'email' => $this->campfire->host->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'address_1' => $this->address1,
            'address_2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
        ]);

        $this->submitted = true;

        $this->dispatchBrowserEvent('next-step');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.send-gift')
            ->layout('layouts.base');
    }
}
