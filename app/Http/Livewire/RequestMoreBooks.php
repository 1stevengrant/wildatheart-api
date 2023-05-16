<?php

namespace App\Http\Livewire;

use App\Mail\Fires\MoreBooksRequested;
use App\Models\CampFire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use WireElements\Pro\Components\Modal\Modal;

class RequestMoreBooks extends Modal
{
    public int|CampFire $fire;

    public function mount(CampFire $fire): void
    {
        $this->fire = $fire;
    }

    public function handle(): void
    {
        Mail::to('hello@digitalevangelist.net')
            ->send(new MoreBooksRequested(fire: $this->fire));

        $this->fire->increment('book_request_count');
        $this->fire->save();

        $this->close();

        $this->dispatchBrowserEvent('notify', 'More books have been requested!');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.request-more-books');
    }
}
