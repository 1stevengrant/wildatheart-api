<?php

namespace App\Http\Livewire;

use App\Actions\User\UpdateUserRole;
use App\Mail\Campfires\Posted;
use App\Models\State;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;

class HostCampfire extends Component
{
    use WithFileUploads;

    public int $step;

    public string $organization = '';

    public string $phone = '';

    public array $photos = [];

    public bool $emailUpdates = true;

    public string $address = '';

    public string $state = '';

    public string $city = '';

    public string $zip = '';

    public string $date = '';

    public string $time = '';

    public bool $isPrivate = false;

    public $states = [];

    public $attendeeLimit = 50;

    public array $times = [];

    public $userEmail = null;

    public string $frequency = '';

    public function mount()
    {
        $this->states = State::all();
        $this->times = generateTimeList();

        if (!auth()->check()) {
            session()->put('userType', User::ROLE_HOST);
            session()->put('currentUrl', url()->current());

            return redirect()->route('social.login', ['provider' => 'auth0']);
        }

        $this->step = auth()->user()->profile_step;
    }

    public function handleStepOne()
    {

    }

    public function handle()
    {

        $this->validate([
            'organization' => 'nullable|min:2|string|max:255',
            'phone' => 'required|digits:10',
            'address' => 'required|min:10',
            'state' => 'required|min:2',
            'city' => 'required|min:2',
            'zip' => 'required|digits:5',
            'attendeeLimit' => 'required|numeric|min:1',
            'isPrivate' => 'required|boolean',
            'frequency' => 'required|string',
        ]);

        $campfire = auth()->user()->campfires()->create([
            'organization' => $this->organization,
            'phone' => $this->phone,
            'address' => $this->address,
            'state' => $this->state,
            'city' => $this->city,
            'zip' => $this->zip,
            'date' => $this->date,
            'time' => $this->time,
            'attendee_limit' => $this->attendeeLimit,
            'is_private' => $this->isPrivate,
            'approved_at' => now(),
            'frequency' => $this->frequency,
        ]);

        $this->reset();

        $this->dispatchBrowserEvent('notify', 'Campfire created successfully! 🔥🔥🔥');

        Mail::to(config('mail.to.address'))->send(new Posted($campfire));

        UpdateUserRole::run(auth()->user(), User::ROLE_HOST);

        return redirect()->route('my.campfires');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.host-campfire')->layout('layouts.base');
    }
}
