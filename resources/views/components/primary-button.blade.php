<button {{ $attributes->merge(['type' => 'submit', 'class' => 'px-4 py-2 border
border-transparent rounded-md bg-orange hover:bg-transparent-orange text-white text-center font-medium transition
ease-in-out duration-150 disabled:bg-grey-200 disabled:text-grey-darker disabled:cursor-not-allowed'])
 }}>
    {{ $slot }}
</button>
