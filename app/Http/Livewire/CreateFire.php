<?php

namespace App\Http\Livewire;

use App\Models\Fire;
use App\Models\State;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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

    public array $times = [];

    public function mount(): void
    {
        $this->states = State::all();
        $this->generateTimeList();
    }

    public function generateTimeList(): void
    {
        $startTime = strtotime('00:00');
        $endTime = strtotime('23:45');

        while ($startTime <= $endTime) {
            $this->times[] = date('H:i', $startTime);
            $startTime += 900;
        }
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
        ]);

        Fire::create([
            'description' => $this->description,
            'phone' => $this->phone,
            'address' => $this->address,
            'state' => $this->state,
            'city' => $this->city,
            'zip' => $this->zip,
            'date' => $this->date,
            'time' => $this->time,
        ]);

        $this->submitted = true;
        $this->reset();

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.create-fire');
    }
}
