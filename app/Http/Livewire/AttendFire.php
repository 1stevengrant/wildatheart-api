<?php

namespace App\Http\Livewire;

use App\Models\CampFire;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendFire extends Component
{
    public string $currentUrl;

    protected $listeners = [
        'registeredForFire' => '$refresh',
        'fireDeleted' => '$refresh',
        'fireUpdated' => '$refresh',
    ];

    public function mount(): void
    {
        $this->currentUrl = url()->current();
    }

    public function handleAttendee($fireId)
    {
        session()->put('userType', User::ROLE_ATTENDEE);
        session()->put('currentUrl', $this->currentUrl);
        session()->put('fireId', $fireId);

        return redirect()->route('social.login', ['provider' => 'auth0']);
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $fires = CampFire::query()->futureState()->get();

        return view('livewire.attend-fire', [
            'fires' => $fires->groupBy('state'),
            'states' => $fires->pluck('state')->unique(),
        ]);
    }
}
