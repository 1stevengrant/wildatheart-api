<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use App\Models\User;
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

    public function handleAttendee($campfireId)
    {
        session()->put('userType', User::ROLE_ATTENDEE);
        session()->put('currentUrl', $this->currentUrl);
        session()->put('campfireId', $campfireId);

        return redirect()->route('social.login', ['provider' => 'auth0']);
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $campfires = Campfire::query()->futureState()->get();

        return view('livewire.list-campfires', [
            'campfires' => $campfires->groupBy('state'),
            'states' => $campfires->pluck('state')->unique(),
        ]);
    }
}
