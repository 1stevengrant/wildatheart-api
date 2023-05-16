<div>
    <ol>
        @foreach($fires as $fire)
            <x-fire :fire="$fire" />
        @endforeach
    </ol>
</div>
