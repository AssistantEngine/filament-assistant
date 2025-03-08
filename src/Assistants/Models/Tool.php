<?php

namespace AssistantEngine\Filament\Assistants\Models;

use AssistantEngine\Filament\Runs\Models\Run;
use AssistantEngine\OpenFunctions\Core\Contracts\AbstractOpenFunction;
use AssistantEngine\OpenFunctions\Core\Contracts\MessageListExtensionInterface;
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

    /**
     * @var callable|null A callable that returns an instance of MessageListExtensionInterface.
     */
    private $extension;

    /**
     * Constructor.
     *
     * @param string   $identifier The tool identifier.
     * @param string   $namespace  The tool namespace.
     * @param string   $description The tool description.
     * @param callable $instance    A callable that returns an OpenFunction instance.
     * @param callable|null $extension (Optional) A closure that returns a MessageListExtensionInterface instance.
     */
    public function __construct(
        string $identifier,
        string $namespace,
        string $description,
        callable $instance,
        ?callable $extension = null
    ) {
        $this->identifier  = $identifier;
        $this->namespace   = $namespace;
        $this->description = $description;
        $this->instance    = $instance;
        $this->extension   = $extension;
    }

    /**
     * Resolve and return the tool instance.
     *
     * This method invokes the stored callable. If the callable expects a parameter,
     * the provided $run object will be passed. Otherwise, it is called without parameters.
     *
     * @param Run|null $run Optional run object.
     * @return AbstractOpenFunction The resolved tool instance.
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

    /**
     * Resolve and return the extension instance.
     *
     * This method invokes the stored extension closure. If the closure expects a parameter,
     * the provided $run object will be passed. Otherwise, it is called without parameters.
     *
     * @param Run|null $run Optional run object.
     * @return MessageListExtensionInterface|null The resolved extension instance, or null if no extension is set.
     */
    public function resolveExtension(Run $run = null): ?MessageListExtensionInterface
    {
        if (!$this->hasExtension()) {
            return null;
        }

        $callable = $this->extension;
        $reflection = new ReflectionFunction($callable);
        if (count($reflection->getParameters()) > 0) {
            return $callable($run);
        }
        return $callable();
    }

    /**
     * Check if this tool has an extension.
     *
     * @return bool True if an extension is set, false otherwise.
     */
    public function hasExtension(): bool
    {
        return isset($this->extension);
    }
}