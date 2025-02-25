<div class="fa-chat-container flex flex-col h-full relative">
    <div class="fa-chat-history flex-1 overflow-y-auto scrollbar-hide" @if($isRunning) wire:poll.visible.1s="loadMessages" @endif>

        @if(empty($messages) && !$isRunning)
            <x-filament-assistant::empty-messages-screen :assistantName="$assistantName" :assistantDescription="$assistantDescription" />
        @endif

        @foreach ($messages as $message)
            @if (isset($message['role']) && $message['role'] === 'user')
                <x-filament-assistant::user-message :message="$message" />
            @elseif (isset($message['role']) && $message['role'] === 'assistant' && isset($message['content']))
                <x-filament-assistant::assistant-message :message="$message" />
            @elseif(isset($message['tool_calls']) && is_array($message['tool_calls']) && count($message['tool_calls']) > 0)
                <!-- Render tool call(s) if available -->
                @foreach($message['tool_calls'] as $toolCall)
                    <x-filament-assistant::tool-call :message="$toolCall" />
                @endforeach
            @else
                <div>Unknown message type</div>
            @endif
        @endforeach

        @if($isRunning)
            <x-filament-assistant::processing-indicator />
        @endif
    </div>

    @if($showScrollIcon)
        <x-filament-assistant::scroll-button />
    @endif

    <div class="fa-chat-input">
        <!-- Input field and send button -->
        <livewire:filament-assistant::chat-input-component :disabled="$isRunning"/>
    </div>


    <style>
        /* For Webkit-based browsers (Chrome, Safari and Opera) */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        /* For IE, Edge and Firefox */
        .scrollbar-hide {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

    </style>

    @script
    <script>
        let chatContainer = document.querySelector('.fa-chat-container .fa-chat-history');
        let autoScrollEnabled = false;

        chatContainer.addEventListener('scroll', function () {
            if (autoScrollEnabled) {
                // as long as autoscroll is enabled the icon should not change
                return;
            }

            checkScrollIconState();
        });

        function checkScrollIconState() {
            if ($wire.showScrollIcon !== shouldShowScrollIcon()) {
                $wire.$set('showScrollIcon', shouldShowScrollIcon())
            }
        }

        function shouldShowScrollIcon() {
            return chatContainer.scrollTop + chatContainer.clientHeight < chatContainer.scrollHeight - 5;
        }

        function isChatContainerAtBottom() {
            return chatContainer.scrollTop + chatContainer.clientHeight >= chatContainer.scrollHeight - 5;
        }

        function onScrollComplete(element, target, callback) {
            const checkScroll = () => {
                  // Allow a small threshold since the scroll value might not match exactly
                if (isChatContainerAtBottom()) {
                    element.removeEventListener('scroll', checkScroll);
                    callback();
                }
            };
            element.addEventListener('scroll', checkScroll);
        }

        function autoScrollToBottom() {
            chatContainer.scrollTo({
                top: chatContainer.scrollHeight,
                behavior: 'smooth'
            });

            onScrollComplete(chatContainer, chatContainer.scrollTop, () => {
                autoScrollEnabled = false;

                checkScrollIconState();
            });
        }


        $wire.on('{{AssistantEngine\Filament\Chat\Components\ChatComponent::EVENT_SHOULD_SCROLL}}', () => {
            autoScrollEnabled = true;
        });

        Livewire.hook('morphed',  ({ el, component }) => {
            if (autoScrollEnabled) {
                autoScrollToBottom();
            }
        })

        chatContainer.scrollTop = chatContainer.scrollHeight;
    </script>
    @endscript
</div>
