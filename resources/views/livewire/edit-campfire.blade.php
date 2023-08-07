<x-layouts.account>
    <div class="mb-6 flex items-center justify-between">
        <x-type.registration-h1>
            <a href="{{ route('my.campfires') }}" class="underline">
                {{ __('My Fires') }}
            </a>
            > {{ __('Editing: ') }} {{ $campfire->name }}
        </x-type.registration-h1>
    </div>

    <x-campfire-card
        class="md:max-w-[400px]"
        :title="$campfire->name . ' Fire'"
        :location="$city"
        :host-name="auth()->user()->full_name"
        :profile-image="
            auth()
                ->user()
                ->profilePhoto()
        "
    />

    <div class="mt-8 space-y-4">
        <div class="max-w-lg space-y-4">
            <x-forms.input
                wire:model="campfire.name"
                label="Name Your Fire"
                required
                maxlength="50"
                instruction="This is the name that will be displayed on your Fire page."
                :error="$errors->first('campfire.name')"
                id="campfireName"
                name="campfireName"
                append="Fire"
            />
        </div>

        <x-map type="Change" :mapboxError="$mapboxError" />

        <div class="space-x-4">
            <x-primary-button
                type="submit"
                class="mt-8"
                wire:click.prevent="handle"
            >
                {{ __('Save Changes') }}
            </x-primary-button>

            <x-secondary-link href="{{ route('my.campfires') }}">
                {{ __('Cancel') }}
            </x-secondary-link>
        </div>
    </div>
</x-layouts.account>
@push('head')
    <link
        href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css"
        rel="stylesheet"
    />
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js"></script>

@endpush

@push('scripts')
    <script>
        document.addEventListener("livewire:load", function () {
            document.title =
                "Editing Fire {!! $campfire->name !!} Fire | {{ config('app.name') }}";
        });
    </script>
    @include('js.update-map')
@endpush
