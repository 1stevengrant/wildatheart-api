<?php

namespace App\Http\Livewire;

use App\Actions\HandleAttendee;
use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListCampfires extends Component
{
    public string $currentUrl;

    protected $listeners = [
        'registeredForCampfire' => '$refresh',
        'campfireDeleted' => '$refresh',
        'campfireUpdated' => '$refresh',
    ];

    public function mount(): void
    {
        $this->currentUrl = url()->current();
    }

    public function handleAttendee($campfireId): void
    {
        HandleAttendee::make()->handle(
            campfireId: $campfireId,
            currentUrl: $this->currentUrl
        );
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $campfires = Campfire::query()->futureState()->get();

        return view('livewire.list-campfires', [
            'campfires' => $campfires->groupBy('state'),
            'states' => $campfires->pluck('state')->unique(),
            'cities' => $campfires->pluck('city')->unique(),
        ]);
    }
}
