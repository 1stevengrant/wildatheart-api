@props([
    /** @var \App\Models\Campfire */
    'campfire'
])

<div {{ $attributes->class(['mt-10 flex items-start space-x-4']) }}>
    <img src="{{ $campfire->hostPhoto() }}"
         class="rounded-lg w-10 h-10 object-scale-down shadow"
         alt="">
    <div>
        <p class="text-sm">Your Host  &middot;</p>
        <p class="text-2xl font-medium">{{ $campfire->host->full_name }}</p>
    </div>
</div>
