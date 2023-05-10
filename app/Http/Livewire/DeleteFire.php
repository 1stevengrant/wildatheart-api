<?php

namespace App\Http\Livewire;

use App\Models\Fire;
use WireElements\Pro\Components\Modal\Modal;

class DeleteFire extends Modal
{
    public int|Fire $fire;

    public function mount(Fire $fire): void
    {
        $this->fire = $fire;
    }

    public function handle(): void
    {
        $this->fire->delete();

        $this->emit('fireDeleted');
        $this->close();
        $this->dispatchBrowserEvent('notify', '🔥 deleted successfully!');
    }

    public function render()
    {
        return view('livewire.delete-fire');
    }
}
