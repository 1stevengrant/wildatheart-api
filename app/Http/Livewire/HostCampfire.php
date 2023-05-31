<?php

namespace App\Http\Livewire;

use App\Actions\User\SendVerificationEmail;
use App\Actions\User\UpdateUserRole;
use App\Mail\Campfires\Posted;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\Redirector;
use Livewire\WithFileUploads;

class HostCampfire extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public string $organization = '';

    public string $campfireName = '';

    public string $phone = '';

    public string $countryCode = '';

    public $photo;

    public bool $emailUpdates = true;

    public string $mapboxPlaceName = '';

    public string $street_address = '';

    public string $state = '';

    public string $city = '';

    public string $zip = '';

    public string $country = '';

    public $latitude = '';

    public $longitude = '';

    public bool $isPrivate = false;

    public bool $isIndoor = false;

    public $attendeeLimit = 50;

    public string $frequency = '';

    public function mount()
    {
        if (! auth()->check()) {
            session()->put('userType', User::ROLE_HOST);
            session()->put('currentUrl', url()->current());

            return redirect()->route('social.login', ['provider' => 'auth0']);
        }
    }

    public function handleStepOne(): void
    {
        $this->validate([
            'photo' => 'required|image',
            'phone' => 'required|min:10|numeric',
            'emailUpdates' => 'required|boolean',
        ]);

        auth()->user()->update([
            'phone' => $this->phone,
            'image' => $this->photo->store('users'),
            'opted_into_marketing' => $this->emailUpdates,
            'profile_step' => 2,
        ]);

        $this->step = 2;
    }

    protected array $messages = [
        'campfireName.required' => 'A fire name is required.',
        'campfireName.min' => 'A fire name must be at least 2 characters.',
        'campfireName.max' => 'A fire name must be less than 255 characters.',
        'mapboxPlaceName.required' => 'The full Fire address is required.',
        'isPrivate.required' => 'Please select if the fire is private or not.',
        'frequency.required' => 'Please select how often the fire will be hosted.',
        'isIndoor.required' => 'Please select if the fire is indoors or not.',
    ];

    public function handleStepTwo(): Redirector
    {
        $this->validate([
            'campfireName' => 'required|min:2|string|max:255',
            'mapboxPlaceName' => 'required|string',
            'isPrivate' => 'required|boolean',
            'frequency' => 'required|string',
            'isIndoor' => 'required|boolean',
        ]);

        $campfire = auth()->user()->campfires()->create([
            'name' => $this->campfireName,
            'mapbox_place_name' => $this->mapboxPlaceName,
            'is_private' => $this->isPrivate,
            'is_indoor' => $this->isIndoor,
            'frequency' => $this->frequency,
        ]);

        Mail::to(config('mail.to.address'))->send(new Posted($campfire));

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
