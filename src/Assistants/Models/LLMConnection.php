<?php

namespace AssistantEngine\Filament\Assistants\Models;

class LLMConnection
{
    public string $identifier;
    public string $url;
    public string $apiKey;

    public function __construct(string $identifier, string $url, string $apiKey)
    {
        $this->identifier = $identifier;
        $this->url = $url;
        $this->apiKey = $apiKey;
    }
}
