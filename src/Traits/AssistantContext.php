<?php

namespace AssistantEngine\Filament\Traits;


use AssistantEngine\Filament\Resolvers\ContextModelResolver;

trait AssistantContext
{
    public function getContextObjectName(): string
    {
        return $this->context_object_name ?? get_class($this);
    }

    public function getContextExclude(): array
    {
        return $this->context_exclude ?? [];
    }

    public static function resolveContextModels($models, $objectName = null)
    {
        $self = new self();

        $result = [];
        $result['data'] = null;

        if (count($models) > 0) {
            $result['data'] = ContextModelResolver::collection($models)->resolve();
        }

        $result['meta'] = [
            'objectName' => $objectName ?? $self->getContextObjectName(),
        ];

        return $result;
    }
}
