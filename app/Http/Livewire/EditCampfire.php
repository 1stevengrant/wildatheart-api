<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use App\Models\State;
use App\Rules\ShouldNotEndWithFire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Repat\LaravelRules\DoesntContainEmojis;
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
            'campfire.name' => [
                'required',
                'min:2',
                'string',
                'max:255',
                new ShouldNotEndWithFire(),
                new DoesntContainEmojis(),
            ],
        ];
    }

    public function handle(): void
    {
        $this->validate();

        $this->campfire->update([
            'name' => trim($this->campfire->name),
        ]);

        $this->emit('campfireUpdated');
        $this->close();
        $this->dispatchBrowserEvent('notify', 'Fire updated successfully');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.edit-campfire');
    }
}
