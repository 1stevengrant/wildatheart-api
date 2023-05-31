<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use App\Models\State;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class EditCampfire extends Modal
{
    public string|Campfire $campfire;

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
    }

    protected function rules(): array
    {
        return [
            'campfire.description' => 'required',
            'campfire.attendee_limit' => 'nullable|numeric',
            'campfire.street_address' => 'required',
            'campfire.city' => 'required',
            'campfire.zip' => 'required',
            'campfire.state' => 'required',
            'campfire.phone' => 'required',
            'campfire.date' => 'required',
            'campfire.time' => 'required',
            'campfire.is_private' => 'nullable|boolean',
        ];
    }

    public function handle(): void
    {
        $this->validate();

        $this->campfire->update([
            'description' => $this->campfire->description,
            'attendee_limit' => $this->campfire->attendee_limit,
            'street_address' => $this->campfire->street_address,
            'city' => $this->campfire->city,
            'zip' => $this->campfire->zip,
            'state' => $this->campfire->state,
            'phone' => $this->campfire->phone,
            'date' => $this->campfire->date,
            'time' => $this->campfire->time,
            'is_private' => $this->campfire->is_private,
        ]);

        $this->emit('campfireUpdated');
        $this->close();
        $this->dispatchBrowserEvent('notify', 'Campfire updated successfully 🔥');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.edit-campfire', [
            'states' => State::all(),
            'times' => generateTimeList(),
        ]);
    }
}
