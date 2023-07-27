<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;

class MyCampfires extends Component
{
    protected $listeners = [
        'campfireDeleted' => '$refresh',
        'campfireUpdated' => '$refresh',
        'copiedLinkToClipboard' => 'copiedLinkToClipboard',
    ];

    public function copiedLinkToClipboard(): void
    {
        $this->dispatchBrowserEvent('notify', [
            'timeout' => 2500,
            'message' => 'Link copied to clipboard!',
        ]);

        $this->dispatchBrowserEvent('close-open-menu');
    }

    public function startAFire(): Redirector|RedirectResponse
    {
        return to_route('host');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.my-campfires', [
            'campfires' => auth()->user()->campfires()->with('host')->get(),
        ]);
    }
}
