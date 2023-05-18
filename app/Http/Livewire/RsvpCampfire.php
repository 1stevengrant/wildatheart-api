<?php

namespace App\Http\Livewire;

use App\Mail\Campfires\AttendeeRegistered;
use App\Models\Attendee;
use App\Models\Campfire;
use App\Services\UserService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use WireElements\Pro\Components\Modal\Modal;

class RsvpCampfire extends Modal
{
    public Campfire|string $campfire;

    public string $phone = '';

    public string $confirmPhone = '';

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
    }

    public function handle(): void
    {
        $this->validate([
            'phone' => 'nullable',
            'confirmPhone' => 'nullable|same:phone',
        ]);

        $attendee = Attendee::create([
            'user_id' => auth()->id(),
            'campfire_id' => $campfire->id,
            'phone' => $this->phone,
        ]);

        $this->emit('registeredForCampfire');

        Mail::to($attendee->user->email)
            ->send(new AttendeeRegistered(
                campfire: $this->campfire,
                attendee: $attendee
            ));

        $this->close();

        $this->dispatchBrowserEvent('notify', 'You have registered 🥳');

        app(UserService::class)->makeUserAttendee($attendee->user->email);

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.rsvp-for-campfire');
    }
}
