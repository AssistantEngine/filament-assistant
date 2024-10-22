<div class="h-full">
    @if ($showWithoutTrigger === false)
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
            <livewire:assistant-engine::chat-component key="filament-assistant::assistant-sidebar-chat" :conversationId="$threadId"/>
        </div>
    @else
        <div class="flex flex-col h-full items-center justify-center text-gray-400">
            <x-filament::loading-indicator class="h-5 w-5" />
            <span class="text-xs mt-3">Loading</span>
        </div>
    @endif
</div>

@script
<script>
    Livewire.on('{{\AssistantEngine\Filament\Components\AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_OPEN}}', () => {
        openChatSidebar();
    })

    Livewire.on('{{\AssistantEngine\Filament\Components\AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_CLOSE}}', () => {
        closeChatSidebar();
    })

    function openChatSidebar() {
        const mainContainer = document.getElementById('filament-assistant::main-container');
        const chatSidebar = document.getElementById('filament-assistant::chat-sidebar');


        // Define the Tailwind classes that need to be added dynamically
        const sidebarClassesToAdd = 'w-full md:w-[400px] z-30';
        const mainContainerClassesToAdjust = 'md:w-[calc(100%-400px)]';

        // Toggle visibility of the sidebar
        if (chatSidebar.classList.contains('hidden')) {
            chatSidebar.classList.remove('hidden');  // Show the chat sidebar

            // Add the necessary classes for responsive widths and z-index using template literals
            chatSidebar.classList.add(...sidebarClassesToAdd.split(' '));  // Apply sidebar classes
            mainContainer.classList.remove('w-full');
            mainContainer.classList.add(...mainContainerClassesToAdjust.split(' '));  // Adjust main container width
        }
    }

    function closeChatSidebar() {
        const mainContainer = document.getElementById('filament-assistant::main-container');
        const chatSidebar = document.getElementById('filament-assistant::chat-sidebar');


        // Define the Tailwind classes that need to be added dynamically
        const sidebarClassesToAdd = 'w-full md:w-[400px] z-30';
        const mainContainerClassesToAdjust = 'md:w-[calc(100%-400px)]';

        // Toggle visibility of the sidebar
        if (chatSidebar.classList.contains('hidden') === false) {
            chatSidebar.classList.add('hidden');  // Hide the chat sidebar

            // Reset the main container to full width when the sidebar is hidden
            chatSidebar.classList.remove(...sidebarClassesToAdd.split(' '));  // Remove sidebar classes
            mainContainer.classList.remove(...mainContainerClassesToAdjust.split(' '));
            mainContainer.classList.add('w-full');  // Set main container to full width
        }
    }
</script>
@endscript
