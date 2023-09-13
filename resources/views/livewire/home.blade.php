<div class="mt-[75px] h-screen w-full text-white md:mt-[107px]">
    <input type="hidden" value="{{ $longitude }}" id="longitude"/>
    <input type="hidden" value="{{ $latitude }}" id="latitude"/>
    <div id="root"></div>
</div>
@push('scripts')
    <script src="{{ asset('js/search.js') }}"></script>
@endpush
