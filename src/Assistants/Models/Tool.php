<?php

namespace AssistantEngine\Filament\Assistants\Models;

use AssistantEngine\OpenFunctions\Core\Contracts\AbstractOpenFunction;

class Tool
{
    public string $identifier;
    public string $namespace;
    public string $description;
    public AbstractOpenFunction $instance;

    public function __construct(string $identifier, string $namespace, string $description, AbstractOpenFunction $instance)
    {
        $this->identifier  = $identifier;
        $this->namespace   = $namespace;
        $this->description = $description;
        $this->instance    = $instance;
    }
}
