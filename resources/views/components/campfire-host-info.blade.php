@props([
    /** @var \App\Models\Campfire */
    'campfire',
])

<div {{ $attributes->class(['mt-10 flex items-start space-x-4']) }}>
    <x-avatar :image="$campfire->hostPhoto()" size="h-20 w-20" />
    <div>
        <p class="text-sm">Your Host &middot;</p>
        <p class="text-2xl font-medium">{{ $campfire->host->full_name }}</p>
    </div>
</div>
