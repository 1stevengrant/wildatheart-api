<?php

namespace App\Http\Livewire;

use App\Mail\Fires\Posted;
use App\Models\Fire;
use App\Models\State;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class CreateFire extends Component
{
    public string $description = '';

    public string $phone = '';

    public string $address = '';

    public string $state = '';

    public string $city = '';

    public string $zip = '';

    public string $date = '';

    public string $time = '';

    public bool $submitted = false;

    public $states = [];

    public $attendeeLimit = 50;

    public array $times = [];

    public function mount(): void
    {
        $this->states = State::all();
        $this->times = generateTimeList();
    }

    public function handle(): void
    {
        $this->validate([
            'description' => 'required|min:10',
            'phone' => 'required|digits:10',
            'address' => 'required|min:10',
            'state' => 'required|min:2',
            'city' => 'required|min:2',
            'zip' => 'required|digits:5',
            'date' => 'required|date|after:today',
            'time' => 'required',
            'attendeeLimit' => 'required|numeric|min:1',
        ]);

        $fire = Fire::create([
            'description' => $this->description,
            'phone' => $this->phone,
            'address' => $this->address,
            'state' => $this->state,
            'city' => $this->city,
            'zip' => $this->zip,
            'date' => $this->date,
            'time' => $this->time,
            'attendee_limit' => $this->attendeeLimit,
        ]);

        $this->submitted = true;
        $this->reset();

        $this->dispatchBrowserEvent('notify', 'Fire created successfully! 🔥🔥🔥');

        Mail::to(config('mail.to.address'))->send(new Posted($fire));
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.create-fire');
    }
}
