<?php

namespace App\Http\Livewire;

use App\Actions\HandleAttendee;
use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ViewCampfire extends Component
{
    public Campfire $campfire;

    public string $currentUrl = '';

    public bool $hideFooter = true;

    public bool $hasConnected = false;

    protected $listeners = [
        'registeredForCampfire' => 'updateConnectionStatus',
    ];

    public function updateConnectionStatus(): void
    {
        $this->hasConnected = true;
    }

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
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
        return view('livewire.view-campfire');
    }
}
