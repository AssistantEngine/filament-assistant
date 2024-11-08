<div class=" z-10">
    @if($visible)
        <div class="relative w-full">
            <div class="fixed bottom-0 right-0  p-4 pr-5">
                <div class="relative p-1 flex" >
                    <x-filament::button class="ml-1" wire:click="openAssistant" :size="$options['size']" :icon="$options['icon']" :color="$options['color']">
                        {{$options['label']}}
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</div>
