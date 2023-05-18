<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class AttendeeList extends Modal
{
    public string|Campfire $campfire;

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.attendee-list');
    }
}
