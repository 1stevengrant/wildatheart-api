<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use App\Rules\ShouldNotEndWithFire;
use App\Traits\LocationTrait;
use App\Traits\MapboxErrorTrait;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;
use Repat\LaravelRules\DoesntContainEmojis;

class EditCampfire extends Component
{
    use LocationTrait, MapboxErrorTrait;

    public string|Campfire $campfire;

    public string $mapboxPlaceName = '';

    public $street_address = '';

    public $state = '';

    public $location = '';

    public $showState = false;

    public $region = '';

    public $district = '';

    public $neighborhood = '';

    public $locality = '';

    public $city = '';

    public $zip = '';

    public $country = '';

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
        $this->city = $campfire->city;
        $this->latitude = $campfire->latitude;
        $this->longitude = $campfire->longitude;

        SEOTools::setTitle('Edit Fire '.$campfire->name);
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
            'campfire.longitude' => 'required',
            'campfire.latitude' => 'required',
        ];
    }

    public function handle(): Redirector|RedirectResponse
    {
        $this->validate();

        $this->campfire->update([
            'name' => trim($this->campfire->name),
            'street_address' => $this->street_address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
        ]);

        $this->emit('campfireUpdated');

        return to_route('my.campfires');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        if ($this->campfire->user_id == auth()->id()) {
            return view('livewire.edit-campfire');
        }

        abort(403, 'You do not own this Fire to make changes');

    }
}
