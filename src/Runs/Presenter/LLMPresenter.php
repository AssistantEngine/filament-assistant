<?php

namespace AssistantEngine\Filament\Runs\Presenter;


use AssistantEngine\Filament\Threads\Models\Thread;
use AssistantEngine\Filament\Assistants\Models\Assistant;
use AssistantEngine\Filament\Threads\Models\Message;
use AssistantEngine\Filament\Threads\Models\ToolCall;
use AssistantEngine\OpenFunctions\Core\Models\Messages\AssistantMessage;
use AssistantEngine\OpenFunctions\Core\Models\Messages\DeveloperMessage;
use AssistantEngine\OpenFunctions\Core\Models\Messages\MessageList;
use AssistantEngine\OpenFunctions\Core\Models\Messages\ToolMessage;
use AssistantEngine\OpenFunctions\Core\Models\Messages\UserMessage;

class LLMPresenter
{
    /**
     * Transforms the thread messages into the OpenAI chat format.
     *
     * The result starts with a system message containing the assistant’s instruction,
     * followed by each user and assistant message from the thread.
     *
     * @param Thread    $thread
     * @param Assistant $assistant
     * @return array
     */
    public static function transformThreadMessages(Thread $thread, Assistant $assistant): MessageList
    {
        // Start with a system/developer message carrying the assistant’s instruction.
        $systemMessage = new DeveloperMessage($assistant->instruction);

        // Create a message list instance.
        $messageList = new MessageList();
        $messageList->addMessage($systemMessage);

        // Loop through each thread message and add the proper Open Functions message type.
        foreach ($thread->messages as $message) {
            if ($message->role === Message::ROLE_USER) {
                $userMessage = new UserMessage($message->content);
                $messageList->addMessage($userMessage);
            } elseif ($message->role === Message::ROLE_ASSISTANT) {
                $assistantMessage = new AssistantMessage($message->content);
                // If there's an associated tool call, attach it to the assistant message.
                $toolResponse = null;
                if ($message->toolCall) {
                    $assistantMessage->addToolCall(
                        new \AssistantEngine\OpenFunctions\Core\Models\Messages\Content\ToolCall(
                            $message->toolCall->call_id,
                            $message->toolCall->call_function,
                            json_encode($message->toolCall->call_arguments)
                        )
                    );

                    if ($message->toolCall->status !== ToolCall::STATUS_IN_PROGRESS) {
                        $toolResponse = new ToolMessage(json_encode($message->toolCall->response_content), $message->toolCall->call_id);
                    }
                }
                $messageList->addMessage($assistantMessage);

                if ($toolResponse) {
                    $messageList->addMessage($toolResponse);
                }
            }
        }

        // Return the message list as an array in the format expected by the LLM.
        return $messageList;
    }
}
