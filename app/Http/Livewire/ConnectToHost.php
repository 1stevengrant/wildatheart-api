<?php

namespace App\Http\Livewire;

use App\Models\Campfire;
use App\Models\State;
use App\Rules\ShouldBeEmpty;
use App\Traits\HoneyPot;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rules\RequiredIf;
use Livewire\Component;
use Spatie\SlackAlerts\Facades\SlackAlert;

class ConnectToHost extends Component
{
    use HoneyPot;

    public Campfire|string $campfire;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $state = '';

    public Collection $states;

    public string $city = '';

    public string $phone = '';

    public string $countryCode = '1';

    public bool $emailUpdates = true;

    public string $country = '';

    public function mount(Campfire $campfire): void
    {
        $this->campfire = $campfire;
        $this->states = State::all();

        $this->prefillFields();
    }

    public function prefillFields(): void
    {
        if (auth()->check()) {
            $this->firstName = auth()->user()->given_name ?? '';
            $this->lastName = auth()->user()->family_name ?? '';
            $this->email = auth()->user()->email;
            $this->phone = auth()->user()->phone ?? '';
            $this->state = auth()->user()->state ?? '';
            $this->city = auth()->user()->city ?? '';
            $this->country = auth()->user()->country ?? '';
        }
    }

    public function handle()
    {
        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|numeric|digits_between:10,15',
            'state' => [
                'string',
                'max:255',
                new RequiredIf($this->country === 'US'),
            ],
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'emailUpdates' => 'required|boolean',
            'countryCode' => 'required|string|max:3',
            'token' => [
                new ShouldBeEmpty(),
            ],
        ]);

        $this->campfire->attendees()->create([
            'user_id' => $this->campfire->host->id,
            'given_name' => trim($this->firstName),
            'family_name' => trim($this->lastName),
            'email' => trim($this->email),
            'phone' => trim($this->phone),
            'city' => trim($this->city),
            'state' => $this->state,
            'country' => getCountryCodeFromCountryName($this->country),
            'country_code' => $this->countryCode,
            'marketing_updates' => $this->emailUpdates,
        ]);

        if (app()->isProduction()) {
            SlackAlert::to('events')
                ->message(trim($this->firstName).' '.trim($this->lastName).' contacted '
                    .$this->campfire->host->fullName.' about his Fire in '.$this->campfire->location.' !');
        }

        $this->emit('connectedToHost');

        return redirect(route('view.campfire', $this->campfire))->with('connected', true);

    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.connect-to-host');
    }
}
