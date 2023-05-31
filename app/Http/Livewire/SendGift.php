<?php

namespace App\Http\Livewire;

use App\Actions\Address\CreateAddress;
use App\Actions\Campfire\UpdateCampfire;
use App\Mail\Campfire\SendGiftEmail;
use App\Models\Campfire;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class SendGift extends Component
{
    public bool $differentMailingAddress = false;

    public string $mapboxPlaceName = '';

    public Campfire|string $campfire;

    public bool $submitted = false;

    public function mount(Campfire $campfire)
    {
        $this->campfire = $campfire;

        if ($campfire->hasRequestedGift()) {
            abort(403, 'You have already requested your gift.');
        }
    }

    protected array $messages = [
        'mapboxPlaceName.required' => 'Please search for a valid address.',
    ];

    public function handle(): void
    {
        if ($this->differentMailingAddress) {

            $this->validate([
                'mapboxPlaceName' => 'required',
            ]);

            CreateAddress::run([
                'mapbox_place_name' => $this->mapboxPlaceName,
                'campfire_id' => $this->campfire->id,
            ]);

            $this->campfire->update([
                'has_different_mailing_address' => true,
            ]);
        } else {
            $this->campfire->update([
                'has_different_mailing_address' => false,
            ]);
        }

        Mail::to($this->campfire->host->email)
            ->send(new SendGiftEmail($this->campfire));

        UpdateCampfire::run(
            data: ['gift_requested' => true],
            campfire: $this->campfire
        );

        $this->submitted = true;
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.send-gift')
            ->layout('layouts.base');
    }
}
