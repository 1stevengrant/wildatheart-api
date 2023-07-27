<?php

namespace App\Http\Livewire;

use App\Models\Address;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class EditAddress extends Modal
{
    public int|Address $address;

    public function mount(Address $address): void
    {
        $this->address = $address;
    }

    protected function rules(): array
    {
        return [
            'address.first_name' => 'required|min:2|string|max:255',
            'address.last_name' => 'required|min:2|string|max:255',
            'address.street_address' => 'required|min:2|string|max:255',
            'address.address2' => 'nullable|min:2|string|max:255',
            'address.company' => 'nullable|min:2|string|max:255',
            'address.city' => 'required|min:2|string|max:255',
            'address.state' => 'required|min:2|string|max:255',
            'address.zip' => 'required|min:2|string|max:255',
            'address.country' => 'required|min:2|string|max:255',
        ];
    }

    public function handle(): void
    {

        $validated = $this->validate();

        $this->address->update($validated);

        $this->close();

        $this->dispatchBrowserEvent('notify', [
            'message' => 'Address updated',
            'timeout' => 5000,
        ]);

        $this->emit('addressUpdated');
    }

    public function render(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('livewire.edit-address');
    }
}
