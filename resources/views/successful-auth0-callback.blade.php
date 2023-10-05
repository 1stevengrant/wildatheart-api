<x-layouts.base>
   <div class="flex h-screen flex-col items-center justify-center">
       <div class="text-center text-2xl font-semibold text-white">
           <div class="flex items-center">
               <x-logo class="mx-auto mb-4 w-[300px]" />
               <img
                   src="{{ asset('svgs/map-campfire-marker.svg') }}"
                   class="mx-auto mb-4 h-[200px]"
                   alt=""
               />
           </div>
           <h2>Please check your email for your login link</h2>
       </div>
   </div>
</x-layouts.base>
