@props([
    /** @var \App\Models\Campfire */
    'campfire',
])

<div {{ $attributes->class(['border border-grey-darker p-8 rounded-lg']) }}>
    <x-button-container>
        <x-primary-link
            href="{{ route('connect.host', $campfire) }}"
            class="w-full">
            {{ __('Connect with') }} {{ $campfire->host->given_name }}
        </x-primary-link>

    </x-button-container>

    <p class="text-sm mt-4 text-center">
        for more information about this Fire
    </p>
</div>
