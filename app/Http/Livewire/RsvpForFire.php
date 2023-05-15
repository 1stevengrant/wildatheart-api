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

class RsvpForFire extends Modal
{
    public CampFire|int $fire;

    public string $phone = '';

    public string $confirmPhone = '';

    public function mount(CampFire $fire): void
    {
        $this->fire = $fire;
    }

    public function handle(): void
    {
        $this->validate([
            'phone' => 'nullable',
            'confirmPhone' => 'nullable|same:phone',
        ]);

        $attendee = Attendee::create([
            'user_id' => auth()->id(),
            'camp_fire_id' => $this->fire->id,
            'phone' => $this->phone,
        ]);

        $this->emit('registeredForFire');

        Mail::to($attendee->user->email)
            ->send(new AttendeeRegistered(
                fire: $this->fire,
                attendee: $attendee
            ));

        $this->close();

        $this->dispatchBrowserEvent('notify', 'You have registered 🥳');

        app(UserService::class)->makeUserAttendee($attendee->user->email);

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.rsvp-for-fire');
    }
}
