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
    private $presenter;

    /**
     * Constructor.
     *
     * @param string   $identifier The tool identifier.
     * @param string   $namespace  The tool namespace.
     * @param string   $description The tool description.
     * @param callable $instance    A callable that returns an OpenFunction instance.
     * @param callable|null $presenter (Optional) A closure that returns a MessageListExtensionInterface instance.
     */
    public function __construct(
        string $identifier,
        string $namespace,
        string $description,
        callable $instance,
        ?callable $presenter = null
    ) {
        $this->identifier  = $identifier;
        $this->namespace   = $namespace;
        $this->description = $description;
        $this->instance    = $instance;
        $this->presenter   = $presenter;
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
        $reflection = new ReflectionFunction($callable(...));
        if (count($reflection->getParameters()) > 0) {
            return $callable($run);
        }
        return $callable();
    }

    /**
     * Resolve and return the extension instance.
     *
     * This method invokes the stored presenter closure. If the closure expects a parameter,
     * the provided $run object will be passed. Otherwise, it is called without parameters.
     *
     * @param Run|null $run Optional run object.
     * @return MessageListExtensionInterface|null The resolved presenter instance, or null if no presenter is set.
     */
    public function resolvePresenter(Run $run = null): ?MessageListExtensionInterface
    {
        if (!$this->hasPresenter()) {
            return null;
        }

        $callable = $this->presenter;
        $reflection = new ReflectionFunction($callable(...));
        if (count($reflection->getParameters()) > 0) {
            return $callable($run);
        }
        return $callable();
    }

    /**
     * Check if this tool has a presenter.
     *
     * @return bool True if a presenter is set, false otherwise.
     */
    public function hasPresenter(): bool
    {
        return isset($this->presenter);
    }
}