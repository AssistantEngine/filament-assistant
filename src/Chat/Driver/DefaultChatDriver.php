<?php

namespace AssistantEngine\Filament\Chat\Driver;

use AssistantEngine\Filament\Assistants\Repositories\AssistantRepository;
use AssistantEngine\Filament\Chat\Contracts\ChatDriverInterface;
use AssistantEngine\Filament\Chat\Models\ConversationOption;
use AssistantEngine\Filament\Chat\Models\ConversationViewModel;
use AssistantEngine\Filament\Runs\Services\RunDispatcherService;
use AssistantEngine\Filament\Threads\Models\Message;
use AssistantEngine\Filament\Threads\Repositories\ThreadRepository;
use AssistantEngine\Filament\Threads\Repositories\MessageRepository;
use AssistantEngine\Filament\Chat\Presenters\MessagePresenter;
use Exception;

class DefaultChatDriver implements ChatDriverInterface
{
    protected ThreadRepository $threadRepository;
    protected MessageRepository $messageRepository;
    private AssistantRepository $assistantRepository;
    private RunDispatcherService $runService;

    public function __construct(
        ThreadRepository $threadRepository,
        MessageRepository $messageRepository,
        AssistantRepository $assistantRepository,
        RunDispatcherService $runService
    ) {
        $this->threadRepository = $threadRepository;
        $this->messageRepository = $messageRepository;
        $this->assistantRepository = $assistantRepository;
        $this->runService = $runService;
    }

    public function findOrCreateConversation(ConversationOption $option): ConversationViewModel
    {
        $assistant = $this->assistantRepository->getAssistantByKey($option->assistantKey);
        if (!$assistant) {
            throw new Exception('Assistant not found');
        }

        $thread = $this->threadRepository->findOrCreate($option);
        $domainMessages = MessagePresenter::getDomainMessages($thread->messages);

        return new ConversationViewModel(
            (string) $thread->id,
            $thread->isRunning(),
            $domainMessages,
            $assistant->name,
            $assistant->description
        );
    }

    public function findConversationByID(string $conversationId): ConversationViewModel
    {
        $thread = $this->threadRepository->findById($conversationId);
        if (!$thread) {
            throw new Exception("Conversation not found");
        }

        // Retrieve the assistant using the thread's assistant key.
        $assistant = $this->assistantRepository->getAssistantByKey($thread->assistant_key);

        if (!$assistant) {
            throw new Exception('Assistant not found');
        }

        $domainMessages = MessagePresenter::getDomainMessages($thread->messages);

        return new ConversationViewModel(
            (string) $thread->id,
            $thread->isRunning(),
            $domainMessages,
            $assistant->name,
            $assistant->description
        );
    }

    public function recreate(string $conversationId): ConversationViewModel
    {
        $thread = $this->threadRepository->findById($conversationId);
        if (!$thread) {
            throw new Exception("Conversation not found");
        }

        $newThread = $this->threadRepository->createFromThread($thread);
        $assistant = $this->assistantRepository->getAssistantByKey($newThread->assistant_key);

        if (!$assistant) {
            throw new Exception('Assistant not found');
        }

        return new ConversationViewModel(
            (string) $newThread->id,
            $thread->isRunning(),
            [],
            $assistant->name,
            $assistant->description
        );
    }

    public function sendMessage(string $conversationId, string $message): ConversationViewModel
    {
        $thread = $this->threadRepository->findById($conversationId);
        if (!$thread) {
            throw new Exception("Conversation not found");
        }

        // Save the user's message.
        $this->messageRepository->addMessage($thread, Message::ROLE_USER, $message);


        // Retrieve the assistant from the thread’s assistant key.
        $assistant = $this->assistantRepository->getAssistantByKey($thread->assistant_key);

        // Run the thread (this will trigger the LLM call and save the assistant's response).
        $this->runService->run($thread, $assistant);

        $domainMessages = MessagePresenter::getDomainMessages($thread->fresh()->messages);
        return new ConversationViewModel((string) $thread->id, $thread->isRunning(), $domainMessages);
    }
}
