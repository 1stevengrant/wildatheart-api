@props([
    /** @var \App\Models\Campfire */
    'campfire',
])

<div {{ $attributes->class(['mt-10 flex items-start space-x-4']) }}>
    <img
        src="{{ $campfire->hostPhoto() }}"
        class="h-10 w-10 rounded-lg object-scale-down shadow"
        alt=""
    />
    <div>
        <p class="text-sm">Your Host &middot;</p>
        <p class="text-2xl font-medium">{{ $campfire->host->full_name }}</p>
    </div>
</div>
