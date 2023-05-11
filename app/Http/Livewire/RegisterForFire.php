<?php

namespace App\Http\Livewire;

use App\Mail\Fires\AttendeeRegistered;
use App\Models\Attendee;
use App\Models\CampFire;
use App\Services\UserService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use WireElements\Pro\Components\Modal\Modal;

class RegisterForFire extends Modal
{
    public CampFire|int $fire;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $confirmEmail = '';

    public string $phone = '';

    public function mount(CampFire $fire): void
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

        $attendee = Attendee::create([
            'camp_fire_id' => $this->fire->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->emit('registeredForFire');

        Mail::to($this->fire->email)
            ->send(new AttendeeRegistered(
                fire: $this->fire,
                attendee: $attendee
            ));

        $this->close();

        $this->dispatchBrowserEvent('notify', 'You have registered 🥳');

        app(UserService::class)->makeUserAttendee($this->email);

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.register-for-fire');
    }
}
