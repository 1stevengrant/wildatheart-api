<button {{ $attributes->merge(['type' => 'submit', 'class' => 'px-4 py-2 border
border-transparent rounded-md bg-white hover:bg-white/80 text-black text-center font-medium transition
ease-in-out duration-150'])
 }}>
    {{ $slot }}
</button>
