<div id="filament-assistant::chat-modal-container" class="flex flex-col h-full dark:bg-neutral-900 dark:text-neutral-100" {!! $maxHeight ? 'style="height: ' . $maxHeight . '"' : '' !!}>
    @if($showTabs)
        <div class="modal-header">
            <ul class="flex justify-between border-b border-gray-300 dark:border-neutral-700">
                <div class="flex">
                    @foreach($tabs as $key => $tab)
                        @if($loop->index < $maxVisibleTabs)
                            <li class="relative mr-3" style="margin-bottom: -2px" wire:key="modal-tab-{{ $key }}">
                                <button
                                    class="inline-flex items-center space-x-2 px-4 py-2 rounded-t-lg {{ $threadId === $key ? 'bg-white text-gray-800 border border-b-1 border-b-white dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 dark:border-b-neutral-900' : 'bg-gray-100 text-gray-500 dark:bg-neutral-800 dark:text-neutral-400' }}"
                                    wire:click="setActiveTab({{ $key }})"
                                >
                                    <span class="truncate max-w-xs" title="{{ $tab['name'] }}">{{ \Illuminate\Support\Str::limit($tab['name'], 10) }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300" viewBox="0 0 20 20" fill="currentColor" wire:click.stop="removeTab({{ $key }})">
                                        <path fill-rule="evenodd" d="M10 9.293l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 011.414-1.414L10 8.586z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </li>
                        @endif
                    @endforeach

                    @if(count($tabs) > $maxVisibleTabs)
                        <li class="relative mr-3" style="margin-bottom: -2px" wire:click.away="$set('showMoreTabs', false)">
                            <button
                                class="inline-flex items-center space-x-2 px-4 py-2 rounded-t-lg bg-gray-100 text-gray-500 dark:bg-neutral-800 dark:text-neutral-400"
                                wire:click="toggleMoreTabs"
                            >
                                <span>{{ count($tabs) - $maxVisibleTabs }} more</span>
                            </button>

                            @if($showMoreTabs)
                                <div class="absolute z-10 mt-2 w-60 bg-white rounded-md shadow-lg py-1 dark:bg-neutral-800 dark:text-neutral-100 dark:shadow-neutral-700">
                                    @foreach($tabs as $key => $tab)
                                        @if($loop->index >= $maxVisibleTabs)
                                            <div class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 dark:text-neutral-300" wire:key="modal-tab-hidden-{{ $key }}">
                                                <a href="#" class="flex-grow" wire:click.prevent="setActiveTab({{ $key }}, true)">
                                                    {{ $tab['name'] }}
                                                </a>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300 cursor-pointer" viewBox="0 0 20 20" fill="currentColor" wire:click.prevent="removeTab({{ $key }})">
                                                    <path fill-rule="evenodd" d="M10 9.293l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414 1.414L8.586 10 4.293 5.707a1 1 0 011.414-1.414L10 8.586z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </li>
                    @endif
                </div>
                <li class="ml-auto">

                </li>
            </ul>
        </div>
    @endif

    <div class="flex flex-row flex-1 overflow-y-hidden">
        @if($showComponent)
            <div class="basis-3/5 overflow-y-scroll">
                <div class="modal-body bg-white p-4 dark:bg-neutral-800">
                    {{-- Show the content for the active tab --}}
                    @if($this->pageClass)
                        <livewire:dynamic-component :is="$this->pageClass" :insideAssistant="true" :record="$this->recordId" key="filament-assistant:assistant-modal-page-component-{{$threadId}}-{{$this->pageClass}}" />
                    @else
                        <p class="dark:text-neutral-200">No Content to show</p>
                    @endif
                </div>
            </div>
            <div class="basis-2/5 border-l p-4 pb-0 dark:border-neutral-700 dark:bg-neutral-900">
                @if($threadId)
                    <livewire:assistant-engine::chat-component key="filament-assistant:assistant-modal-chat" :conversationId="$threadId"/>
                @endif
            </div>
        @else
            <div class="w-full p-4 pb-0 dark:bg-neutral-900 dark:text-neutral-200">
                @if($threadId)
                    <livewire:assistant-engine::chat-component key="filament-assistant:assistant-modal-chat" :conversationId="$threadId"/>
                @endif
            </div>
        @endif
    </div>
</div>

@script
<script>
    initMaxHeight();

    // init the max height and indicate that component should scroll
    function initMaxHeight() {
        let operationContainer = document.getElementById('filament-assistant::chat-modal-container');
        operationContainer.style.height = window.innerHeight - 200 + 'px';

        $wire.set("maxHeight", operationContainer.style.height)
    }
</script>
@endscript
