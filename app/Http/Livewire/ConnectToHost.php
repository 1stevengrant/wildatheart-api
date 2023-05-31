<?php

namespace App\Http\Livewire;

use App\Actions\User\CreateUser;
use App\Actions\User\UpdateUser;
use App\Actions\User\UpdateUserRole;
use App\Mail\Campfire\AttendeeRegistered;
use App\Models\Campfire;
use App\Models\State;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ConnectToHost extends Component
{
    public Campfire|string $campfire;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $confirmEmail = '';

    public string $state = '';

    public Collection $states;

    public string $city = '';

    public string $phone = '';

    public string $confirmPhone = '';

    public bool $emailUpdates = true;

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
        $this->states = State::all();
    }

    public function handle(): void
    {
        if (auth()->check()) {
            $attendee = $this->registerExistingUser();
        } else {
            $attendee = $this->registerGuest();
        }

        Mail::to($attendee->user->email)
            ->send(new AttendeeRegistered(
                campfire: $this->campfire,
                attendee: $attendee
            ));

        $this->emit('closeConnectToHost');

        $this->dispatchBrowserEvent('notify', 'You have connected with '.$this->campfire->host->given_name.'!');
        $this->dispatchBrowserEvent('registered-for-campfire');
        $this->emit('registeredForCampfire');

        UpdateUserRole::make()->handle($attendee->user, User::ROLE_ATTENDEE);

        $this->resetExcept('campfire', 'states');

    }

    public function registerExistingUser()
    {
        $this->validate([
            'phone' => 'nullable',
            'confirmPhone' => 'nullable|same:phone',
        ]);

        UpdateUser::make()->handle(auth()->user(), [
            'phone' => $this->phone,
            'opted_into_marketing' => $this->emailUpdates,
        ]);

        return $this->campfire->attendees()->create([
            'user_id' => auth()->id(),
        ]);
    }

    public function registerGuest()
    {
        $this->validate([
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email|unique:users,email',
            'confirmEmail' => 'required|same:email',
            'state' => 'required',
            'city' => 'required',
            'phone' => 'required',
            'confirmPhone' => 'required|same:phone',
        ]);

        $user = CreateUser::make()->handle([
            'given_name' => $this->firstName,
            'family_name' => $this->lastName,
            'email' => $this->email,
            'state' => $this->state,
            'city' => $this->city,
            'phone' => $this->phone,
            'opted_into_marketing' => $this->emailUpdates,
        ]);

        return $this->campfire->attendees()->create([
            'user_id' => $user->id,
        ]);
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.connect-to-host');
    }
}
