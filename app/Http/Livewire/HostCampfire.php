<?php

namespace App\Http\Livewire;

use App\Actions\User\SendVerificationEmail;
use App\Actions\User\UpdateUserRole;
use App\Models\User;
use App\Rules\ShouldBeEmpty;
use App\Rules\ShouldNotEndWithFire;
use App\Traits\HoneyPot;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Redirector;
use Livewire\WithFileUploads;
use Repat\LaravelRules\DoesntContainEmojis;

class HostCampfire extends Component
{
    use WithFileUploads, HoneyPot;

    public int $step = 1;

    public string $firstName = '';

    public string $lastName = '';

    public string $organization = '';

    public string $campfireName = '';

    public string $phone = '';

    public string $countryCode = '1';

    public $photo;

    public bool $emailUpdates = true;

    public string $mapboxPlaceName = '';

    public $street_address = '';

    public $state = '';

    public $location = '';

    public $showState = false;

    public $city = '';

    public $zip = '';

    public $country = '';

    public $latitude = '';

    public $longitude = '';

    public string $frequency = 'monthly';

    public function mount()
    {
        if (! auth()->check()) {
            session()->put('userType', User::ROLE_HOST);
            session()->put('currentUrl', url()->current());

            return redirect()->route('social.login', ['provider' => 'auth0']);
        }

        $user = auth()->user();

        $this->firstName = $user->given_name;
        $this->lastName = $user->family_name;
        $this->campfireName = $user->given_name."'s";
        $this->countryCode = $user->country_code ?? '1';
        $this->phone = $user->phone ?? '';
    }

    public function updated(): void
    {
        if ($this->showState) {
            $this->location = $this->location = $this->city.', '.$this->state;
        } else {
            $this->location = $this->location = $this->city;
        }
    }

    public function handleStepOne(): void
    {
        $this->validate([
            'firstName' => 'required|string|min:2|max:255',
            'lastName' => 'required|string|min:2|max:255',
            'phone' => 'required|numeric|digits_between:10,15',
            'emailUpdates' => 'required|boolean',
            'countryCode' => 'required|string',
            'token' => [
                new ShouldBeEmpty(),
            ],
        ]);

        auth()->user()->update([
            'given_name' => $this->firstName,
            'family_name' => $this->lastName,
            'phone' => $this->phone,
            'country_code' => $this->countryCode,
            'image' => $this->photo ? $this->photo->store('users') : auth()->user()->image,
            'opted_into_marketing' => $this->emailUpdates,
            'profile_step' => 2,
        ]);

        $this->step = 2;

        $this->dispatchBrowserEvent('next-step');
    }

    public function messages(): array
    {
        return [
            'campfireName.required' => 'A fire name is required.',
            'campfireName.min' => 'A fire name must be at least 2 characters.',
            'campfireName.max' => 'A fire name must be less than 255 characters.',
            'mapboxPlaceName.required' => 'The full Fire address is required.',
            'frequency.required' => 'Please select how often the fire will be hosted.',
        ];
    }

    public function handleStepTwo(): Redirector
    {
        $this->validate([
            'campfireName' => [
                'required',
                'min:2',
                'string',
                'max:255',
                new ShouldNotEndWithFire(),
                new DoesntContainEmojis(),
            ],
            'mapboxPlaceName' => 'required|string',
            'street_address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'frequency' => 'required|string',
            'token' => [
                new ShouldBeEmpty(),
            ],
        ]);

        $campfire = auth()->user()->campfires()->create([
            'name' => $this->campfireName,
            'mapbox_place_name' => $this->mapboxPlaceName,
            'frequency' => $this->frequency,
            'street_address' => $this->street_address,
            'state' => $this->state,
            'city' => $this->city,
            'zip' => $this->zip,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        UpdateUserRole::make()->handle(
            user: auth()->user(),
            role: User::ROLE_HOST
        );

        if (! auth()->user()->hasVerifiedEmail()) {
            SendVerificationEmail::make()->handle(campfire: $campfire);

            return redirect()->route('confirm.email', ['campfire' => $campfire]);
        }

        return redirect()->route('gift', ['campfire' => $campfire]);
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.host-campfire')
            ->layout('layouts.base');
    }
}
