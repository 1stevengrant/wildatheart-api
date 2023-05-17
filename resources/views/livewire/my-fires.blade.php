<div>
    @unless($fires->count())
        <p class="text-center text-2xl">
            You have no fires.
        </p>
    @else
        <ol>
            @foreach($fires as $fire)
                <x-fire :fire="$fire"/>
            @endforeach
        </ol>
    @endunless
</div>

@push('scripts')
    <script>
    document.addEventListener('livewire:load', function () {
        document.title = 'My 🔥🔥🔥 | {{ config('app.name') }}';
    });
    </script>
@endpush
