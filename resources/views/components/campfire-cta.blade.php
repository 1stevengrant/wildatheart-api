@props([
    /** @var \App\Models\Campfire */
    'campfire',
])

<div {{ $attributes->class(['rounded-lg border border-grey-darker p-8']) }}>
    <x-button-container>
        <x-primary-link
            href="{{ route('connect.host', $campfire) }}"
            class="w-full"
        >
            {{ __('Connect with') }} {{ $campfire->host->given_name }}
        </x-primary-link>
    </x-button-container>

    <p class="mt-4 text-center text-sm">for more information about this Fire</p>
</div>
