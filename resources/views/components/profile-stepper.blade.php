@props([
    'step',
])
<div {{ $attributes->merge(['class' => 'grid grid-cols-3 gap-2']) }}>
    <div
        class="@if($step === 1 || $step === 2 || $step === 3 ) bg-white
    @endif h-3 rounded-full border border-white"
    ></div>
    <div
        class="@if($step === 2 || $step === 3) bg-white @endif h-3 rounded-full border border-white"
    ></div>
    <div
        class="@if($step === 3 ) bg-white @endif h-3 rounded-full border border-white"
    ></div>
</div>
