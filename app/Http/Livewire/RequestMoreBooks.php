<?php

namespace App\Http\Livewire;

use App\Mail\Campfire\MoreBooksRequested;
use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use WireElements\Pro\Components\Modal\Modal;

class RequestMoreBooks extends Modal
{
    public string|Campfire $campfire;

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
    }

    public function handle(): void
    {
        Mail::to('hello@digitalevangelist.net')
            ->send(new MoreBooksRequested(campfire: $this->campfire));

        $this->campfire->increment('book_request_count');
        $this->campfire->save();

        $this->close();

        $this->dispatchBrowserEvent('notify', [
            'message' => 'More books have been requested!',
            'timeout' => 5000,
        ]);
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.request-more-books');
    }
}
