<label for="">
    <input type="text"
           wire:model="token"
           class="text-black"
           @if(app()->isProduction())
               style="display: none"
        @endif>
</label>
