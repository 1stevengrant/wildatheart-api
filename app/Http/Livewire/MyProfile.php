<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Repat\LaravelRules\DoesntContainEmojis;

class MyProfile extends Component
{
    use WithFileUploads;

    public int|User $user;

    public $photo;

    public bool $isUploaded = false;

    public function mount(): void
    {
        $this->user = auth()->user();
        $this->photo = $this->user->image;
    }

    public function updatedPhoto(): void
    {
        $this->validate([
            'photo' => 'image|max:10000',
        ]);

        $this->isUploaded = true;
    }

    protected function rules(): array
    {
        return [
            'user.given_name' => [
                'required',
                'min:2',
                'string',
                'max:255',
                new DoesntContainEmojis(),
            ],
            'user.family_name' => [
                'required',
                'string',
                'max:255',
                new DoesntContainEmojis(),
            ],
            'user.country_code' => 'required|string|max:255',
            'user.phone' => 'required|string|max:255',
            'user.opted_into_marketing' => 'boolean|required',
        ];
    }

    public function handle(): void
    {
        $this->validate();

        $this->user->save();

        auth()->user()->update([
            'given_name' => trim($this->user->given_name),
            'family_name' => trim($this->user->family_name),
            'phone' => trim($this->user->phone),
            'country_code' => $this->user->country_code,
            'image' => $this->isUploaded ? $this->photo->store('users') : auth()->user()->image,
            'opted_into_marketing' => $this->user->opted_into_marketing,
        ]);

        $this->dispatchBrowserEvent('notify', [
            'message' => 'Your profile has been updated.',
            'timeout' => 5000,
        ]);
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.my-profile');
    }
}
