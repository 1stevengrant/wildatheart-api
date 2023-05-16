<div>
    <x-type.h1>
        {{ ucwords(str_replace('+', ' ', $state)) }}
    </x-type.h1>
    <ul class="mt-6">
        @foreach($fires as $fire)
            <x-fire :fire="$fire" class="odd:bg-gray-100 p-4"/>
        @endforeach
    </ul>
</div>
