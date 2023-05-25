@props([
    /** @var \App\Models\Campfire */
    'campfire'
])

<div {{ $attributes->class(['border border-grey-darker p-8 rounded-lg']) }}>
    <h2 class="mb-4 text-xl font-medium text-center">Request an Invitation</h2>
    <div class="flex flex-col items-center justify-center">
        <x-primary-button class="w-full">
            Connect with {{ $campfire->host->given_name }}
        </x-primary-button>
    </div>

    <p class="text-sm mt-4 text-center">
        for more information about this Fire
    </p>
</div>
