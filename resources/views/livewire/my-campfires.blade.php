<div>
    @unless($campfires->count())
        <p class="text-center text-2xl">
            You have no campfires.
        </p>
    @else
        <ol>
            @foreach($campfires as $campfire)
                <x-campfire :campfire="$campfire"/>
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
