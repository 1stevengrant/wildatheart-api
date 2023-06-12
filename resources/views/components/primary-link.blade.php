<a
    {{
        $attributes->merge(['class' => 'px-4 py-2 border inline-block
        border-transparent rounded-md bg-orange hover:bg-transparent-orange text-white text-center font-medium transition
        w-full ease-in-out duration-150 disabled:bg-grey-200 disabled:text-grey-darker disabled:cursor-not-allowed'])
    }}
>
    {{ $slot }}
</a>
