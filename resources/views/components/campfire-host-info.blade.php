@props([
    /** @var \App\Models\Campfire */
    'campfire'
])

<div {{ $attributes->class(['mt-10 flex items-start space-x-6']) }}>
    <img src="{{ gravatar($campfire->host->email)  }}"
         class="rounded-lg w-[54px] h-[54px]"
         alt="">
    <div>
        <p class="text-sm">Your Host  &middot;</p>
        <p class="text-2xl font-medium">{{ $campfire->host->full_name }}</p>
    </div>
</div>
