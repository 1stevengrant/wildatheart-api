@props([
    /** @var \App\Models\Campfire */
    'campfire'
])

<div {{ $attributes->class(['mt-10 flex items-start space-x-2']) }}>
    <img src="{{ \Creativeorange\Gravatar\Facades\Gravatar::get('hello@digitalevangelist.net') }}"
         class="rounded-lg w-[54px] h-[54px]"
         alt="">
    <div>
        <p class="text-sm">Your Host</p>
        <p class="text-2xl font-medium">{{ $campfire->host->full_name }}</p>
    </div>
</div>
