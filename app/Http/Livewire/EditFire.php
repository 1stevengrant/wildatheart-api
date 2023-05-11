<?php

namespace App\Http\Livewire;

use App\Models\CampFire;
use App\Models\State;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class EditFire extends Modal
{
    public int|CampFire $campfire;

    public function mount(CampFire $fire): void
    {
        $this->campfire = $fire;
    }

    protected function rules(): array
    {
        return [
            'campfire.description' => 'required',
            'campfire.attendee_limit' => 'nullable|numeric',
            'campfire.address' => 'required',
            'campfire.city' => 'required',
            'campfire.zip' => 'required',
            'campfire.state' => 'required',
            'campfire.phone' => 'required',
            'campfire.date' => 'required',
            'campfire.time' => 'required',
        ];
    }

    public function handle(): void
    {
        $this->validate();

        $this->campfire->update([
            'description' => $this->campfire->description,
            'attendee_limit' => $this->campfire->attendee_limit,
            'address' => $this->campfire->address,
            'city' => $this->campfire->city,
            'zip' => $this->campfire->zip,
            'state' => $this->campfire->state,
            'phone' => $this->campfire->phone,
            'date' => $this->campfire->date,
            'time' => $this->campfire->time,
        ]);

        $this->emit('fireUpdated');
        $this->close();
        $this->dispatchBrowserEvent('notify', 'Campfire updated successfully 🔥');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.edit-fire', [
            'states' => State::all(),
            'times' => generateTimeList(),
        ]);
    }
}
