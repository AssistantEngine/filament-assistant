<div id="filament-assistant::chat-sidebar"
     @class([
        "border-l dark:border-gray-800 fixed right-0 bottom-0 top-0 overflow-y-scroll z-30",
        "hidden" => !$visible,
    ])  style="width: {{ $visible ? $width . 'px' : 'auto' }};">
    <div class="h-full bg-white dark:bg-gray-900">
        @if ($openByDefault === false)
            <div class="absolute end-3 top-3 cursor-pointer z-40">
                <button class=" text-gray-400" wire:click="closeSidebar()">
                    <svg class="fi-icon-btn-icon h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        @endif

        @if($threadId)
            <div class="p-4 h-full">
                <livewire:filament-assistant::chat-component key="filament-assistant::assistant-sidebar-chat" :conversationId="$threadId"/>
            </div>
        @endif
    </div>
</div>


@script
<script>


    Livewire.on('{{\AssistantEngine\Filament\Chat\Components\Sidebar::EVENT_ASSISTANT_SIDEBAR_OPEN}}', () => {
        openChatSidebar();
    })

    Livewire.on('{{\AssistantEngine\Filament\Chat\Components\Sidebar::EVENT_ASSISTANT_SIDEBAR_CLOSE}}', () => {
        closeChatSidebar();
    })

    Livewire.on('{{\AssistantEngine\Filament\Chat\Components\ChatComponent::EVENT_RUN_FINISHED}}', () => {
        let components = Livewire.all();

        components.forEach(component => {
            if (component.el.classList.contains('fi-page')) {
                component.$wire.$refresh();
            }
        });
    })

    function openChatSidebar() {
        const chatSidebar = document.getElementById('filament-assistant::chat-sidebar');
        const mainContainer = document.getElementById('filament-assistant::main-container');
        const topbarDiv = document.getElementById('filament-assistant::topbar-container');

        const width = @json($width);  // Pass the PHP width variable to JavaScript

        if (chatSidebar.classList.contains('hidden')) {
            chatSidebar.classList.remove('hidden');  // Show the chat sidebar

            // Apply dynamic width using JavaScript instead of Tailwind
            chatSidebar.style.width = `${width}px`;
            mainContainer.style.width = `calc(100% - ${width}px)`;

            if (topbarDiv) {
                topbarDiv.style.width = `calc(100% - ${width}px)`;
            }
        }
    }

    function closeChatSidebar() {
        const mainContainer = document.getElementById('filament-assistant::main-container');
        const chatSidebar = document.getElementById('filament-assistant::chat-sidebar');
        const topbarDiv = document.getElementById('filament-assistant::topbar-container');

        chatSidebar.classList.add('hidden');  // Hide the chat sidebar

        // Reset main container width
        mainContainer.style.width = '100%';
        if (topbarDiv) {
            topbarDiv.style.width = '100%';
        }
    }
</script>
@endscript
