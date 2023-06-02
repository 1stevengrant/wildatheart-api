<div>
    <x-breadcrumbs
        :city="ucwords(str_replace('+', ' ', $city))" />
    <x-type.h1>
        Fire in {{ ucwords(str_replace('+', ' ', $city)) }}
    </x-type.h1>
    <ul class="mt-6">
        @foreach($campfires as $campfire)
            <x-campfire
                :campfire="$campfire"
                class="odd:bg-gray-100 p-4"/>
        @endforeach
    </ul>
</div>
