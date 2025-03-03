<?php

namespace AssistantEngine\Filament\Assistants\Models;

use AssistantEngine\Filament\Runs\Models\Run;
use AssistantEngine\OpenFunctions\Core\Contracts\AbstractOpenFunction;
use ReflectionFunction;

/**
 * Class Tool
 *
 * Represents a tool that can resolve its instance using a callable.
 */
class Tool
{
    public string $identifier;
    public string $namespace;
    public string $description;
    /**
     * @var callable A callable that returns an instance of an OpenFunction.
     */
    private $instance;

    public function __construct(string $identifier, string $namespace, string $description, callable $instance)
    {
        $this->identifier  = $identifier;
        $this->namespace   = $namespace;
        $this->description = $description;
        $this->instance    = $instance;
    }

    /**
     * Resolve and return the tool instance.
     *
     * This method invokes the stored callable. If the callable expects a parameter,
     * the provided $run object will be passed. Otherwise, it is called without parameters.
     *
     * @param mixed|null $run Optional run object.
     * @return mixed The resolved tool instance.
     */
    public function resolveInstance(Run $run = null): AbstractOpenFunction
    {
        $callable = $this->instance;
        $reflection = new ReflectionFunction($callable);
        if (count($reflection->getParameters()) > 0) {
            return $callable($run);
        }
        return $callable();
    }
}