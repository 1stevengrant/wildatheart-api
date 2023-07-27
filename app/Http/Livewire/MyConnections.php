<?php

namespace App\Http\Livewire;

use App\Models\Attendee;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class MyConnections extends Component
{
    protected $listeners = [
        'copiedEmailsToClipboard' => 'copiedEmailsToClipboard',
        'attendeeContactedToggled' => '$refresh',
        'copiedLink' => 'copiedLink',
    ];

    public function copiedEmailsToClipboard(): void
    {
        $this->dispatchBrowserEvent('notify', [
            'message' => 'Copied emails to clipboard',
            'timeout' => 5000,
        ]);
    }

    public function copiedLink(): void
    {
        $this->dispatchBrowserEvent('notify', [
            'message' => 'Copied link to clipboard',
            'timeout' => 5000,
        ]);
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $campfires = auth()->user()->campfires()->with(['attendees' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->get();

        $allConnections = auth()->user()->attendees->sortBy('family_name');

        return view('livewire.my-connections', [
            'campfires' => $campfires,
            'allConnections' => $allConnections,
            'allConnectionsEmails' => implode(',', $allConnections->pluck('email')->toArray()),
        ]);
    }

    public function toggleContactedStatus(Attendee $attendee): void
    {
        if ($attendee->hasBeenContacted()) {
            $attendee->updateQuietly([
                'contacted_on' => null,
            ]);
        } else {
            $attendee->updateQuietly([
                'contacted_on' => now(),
            ]);
        }

        $this->emit('attendeeContactedToggled');
    }
}
