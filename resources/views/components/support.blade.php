<button
    class="flex items-center space-x-2 text-white transition-colors duration-150 ease-in-out hover:text-orange focus:outline-none focus:ring-orange"
    onclick="Livewire.emit('modal.open', 'report-issue')"
>
    <x-icons.questions />
    <span>{{ __('Feedback') }}</span>
</button>
