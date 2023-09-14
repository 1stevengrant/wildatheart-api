<div class="mt-[75px] h-screen w-full text-white md:mt-[107px] flex flex-col" id="livewire-home">
    <input type="hidden" value="{{ $longitude }}" id="longitude"/>
    <input type="hidden" value="{{ $latitude }}" id="latitude"/>
    <div id="root" class="flex flex-col h-full overflow-hidden"></div>
</div>
@push('scripts')
    <script src="{{ asset('js/search.js') }}"></script>
    <script>
    const livewireHome = document.getElementById('livewire-home');
    const footer = document.getElementById('footer');
    const header = document.getElementById('site-header');
    livewireHome.style.height = window.innerHeight - (header.offsetHeight + footer.offsetHeight) + 'px';
    window.addEventListener('resize', () => {
        livewireHome.style.height = window.innerHeight - (header.offsetHeight + footer.offsetHeight) + 'px';
    });
    </script>
@endpush
