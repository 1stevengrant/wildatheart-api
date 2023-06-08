<button class="flex items-center text-white hover:text-orange transition-colors ease-in-out duration-150 space-x-2 focus:ring-orange focus:outline-none"
        onclick="Livewire.emit('modal.open', 'report-issue')">
    <x-icons.questions />
    <span>{{ __('Feedback') }}</span>
</button>
