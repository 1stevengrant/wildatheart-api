@props([
    'image',
    'size' => false,
])
<div
    class="{{ $size ? $size : 'h-6 w-6' }} flex-shrink-0 rounded-full bg-cover bg-center object-fill"
    style="background-image: url('{{ $image }}')"
></div>
