<div class="flex flex-row">
    <div class="w-full">
        <div class="flex rounded-lg shadow-sm border border-gray-300 dark:border-neutral-700">
            <x-filament::icon-button
                icon="heroicon-o-trash"
                wire:click="resetConversation"
                label="Reset Thread"
                color="gray"
                size="xs"
                class="m-0 my-auto ml-2"
            />


            <form wire:submit.prevent="sendMessage" class="w-full dark:text-gray-200">
                <input
                    type="text"
                    wire:model.live="messageInput"
                    placeholder="Type your message here..."
                    class="py-3 px-4 block w-full text-sm focus:ring-0 border-white dark:bg-gray-900 dark:border-gray-800 dark:border-l-gray-900 dark:border-r-gray-900 focus-visible:border-white">
            </form>


            <x-filament::icon-button
                icon="heroicon-s-arrow-up-circle"
                label="Reset Thread"
                wire:click="sendMessage"

                color="gray"
                :disabled="!$messageInput || $disabled"
                size="sm"
                class="m-0 my-auto mr-2"
            />

        </div>
    </div>
</div>
