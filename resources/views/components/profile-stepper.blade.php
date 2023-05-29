@props([
    'step'
])
<div {{ $attributes->merge(['class' => 'grid grid-cols-3 gap-2']) }}>
    <div class="h-3 border border-white rounded-full @if($step === 1 || $step === 2 || $step === 3 ) bg-white
    @endif"></div>
    <div class="h-3 border border-white rounded-full @if($step === 2 || $step === 3) bg-white @endif"></div>
    <div class="h-3 border border-white rounded-full @if($step === 3 ) bg-white @endif"></div>
</div>
