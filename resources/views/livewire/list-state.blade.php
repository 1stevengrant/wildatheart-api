<div>
    <x-breadcrumbs
        :state="ucwords(str_replace('+', ' ', $state))" />

    <x-type.h1 class="mt-4">
        {{ ucwords(str_replace('+', ' ', $state)) }}
    </x-type.h1>
    <ul class="mt-6">
        @foreach($campfires as $campfire)
            <x-campfire
                :campfire="$campfire"
                class="odd:bg-gray-100 p-4"/>
        @endforeach
    </ul>
</div>
