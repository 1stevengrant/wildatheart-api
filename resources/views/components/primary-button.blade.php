<button
    {{
        $attributes->merge(['type' => 'submit', 'class' => 'px-2 py-1 md:px-4 md:py-2  text-sm md:text-base border
        border-transparent rounded-md bg-orange hover:bg-transparent-orange text-white text-center font-medium transition
        ease-in-out duration-150 disabled:bg-grey-200 disabled:text-grey-darker disabled:cursor-not-allowed'])
    }}
>
    {{ $slot }}
</button>
