<?php

namespace AssistantEngine\Filament\Assistants\Models;

class Assistant
{
    public string $key;
    public string $name;
    public string $instruction;
    public string $description;
    public LLMConnection $llmConnection;
    public string $model;
    /**
     * @var Tool[]
     */
    public array $tools; // Array of Tool model instances

    public function __construct(
        string $key,
        string $name,
        string $instruction,
        string $description,
        LLMConnection $llmConnection,
        string $model,
        array $tools = []  // Default to an empty array if no tools are provided.
    ) {
        $this->key          = $key;
        $this->name         = $name;
        $this->instruction  = $instruction;
        $this->description  = $description;
        $this->llmConnection = $llmConnection;
        $this->model        = $model;
        $this->tools        = $tools;
    }
}
