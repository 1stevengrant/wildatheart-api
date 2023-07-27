<x-layouts.account>
    <div class="mb-6 flex items-center justify-between">
        <x-type.registration-h1>
            {{ __('My Addresses') }}
        </x-type.registration-h1>

        {{-- <x-primary-button wire:click="startAFire"> --}}
        {{-- {{ __('Start a Fire') }} --}}
        {{-- </x-primary-button> --}}
    </div>

    @unless ($addresses->count())
        <p class="text-center text-2xl">
            {{ __('You have no addresses currently.') }}
        </p>
    @else
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($addresses as $address)
                <div
                    class="flex items-center justify-between rounded-md border border-grey-darker p-4 shadow-md"
                >
                    <div>
                        <p>
                            {{ $address->fullName }}
                        </p>
                        <address>
                            {{ $address->street_address }}
                            <br />
                            {{ $address->city }}, {{ $address->state }}
                            {{ $address->postal_code }}
                            <br />
                            {{ $address->country }}
                        </address>
                    </div>
                    <x-primary-button
                        wire:click="$emit('modal.open', 'edit-address', {'address' : '{{ $address->id }}'})"
                        class="mt-4 inline-block"
                    >
                        {{ __('Edit Address') }}
                    </x-primary-button>
                </div>
            @endforeach
        </div>
    @endunless
</x-layouts.account>

@push('scripts')
    <script>
        document.addEventListener("livewire:load", function () {
            document.title = "My Addresses | {{ config('app.name') }}";
        });
    </script>
@endpush
