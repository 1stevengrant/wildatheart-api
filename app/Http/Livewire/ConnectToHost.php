<?php

namespace App\Http\Livewire;

use App\Actions\UserCreate;
use App\Actions\UserUpdate;
use App\Actions\UserUpdateRole;
use App\Mail\Campfires\AttendeeRegistered;
use App\Models\Campfire;
use App\Models\State;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use WireElements\Pro\Components\Modal\Modal;

class ConnectToHost extends Modal
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

        $this->emit('registeredForCampfire');

        Mail::to($attendee->user->email)
            ->send(new AttendeeRegistered(
                campfire: $this->campfire,
                attendee: $attendee
            ));

        $this->close();

        $this->dispatchBrowserEvent('notify', 'You have registered 🥳');

        UserUpdateRole::run($attendee->user, User::ROLE_ATTENDEE);

    }

    public function registerExistingUser()
    {
        $this->validate([
            'phone' => 'nullable',
            'confirmPhone' => 'nullable|same:phone',
        ]);

        UserUpdate::run(auth()->user(), [
            'phone' => $this->phone,
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

        $user = UserCreate::run([
            'given_name' => $this->firstName,
            'family_name' => $this->lastName,
            'email' => $this->email,
            'state' => $this->state,
            'city' => $this->city,
            'phone' => $this->phone,
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
