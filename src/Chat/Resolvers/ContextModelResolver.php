<?php

namespace AssistantEngine\Filament\Chat\Resolvers;

use AssistantEngine\Filament\Chat\Contracts\ContextModelInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContextModelResolver extends JsonResource
{
    public function toArray(Request $request): ?array
    {
        if (!$this->implementsContextModelInterface()) {
            return null;
        }

        $relations = $this->resource->getRelations();
        $this->resource->unsetRelations();

        $data = parent::toArray($request);

        $data = $this->resolveRelations($data, $relations);

        return $this->applyContextExclusions($data);
    }

    protected function implementsContextModelInterface(): bool
    {
        return in_array(ContextModelInterface::class, class_implements($this->resource));
    }

    protected function resolveRelations(array $data, array $relations): array
    {
        foreach ($relations as $relationName => $models) {
            $relatedClass = get_class($this->resource->$relationName()->getRelated());

            $data[$relationName] = $this->resolveRelationData($relatedClass, $models);
        }

        return $data;
    }

    protected function resolveRelationData(string $relatedClass, $models): array
    {
        if (!is_iterable($models)) {
            $models = [$models];
        }

        if (in_array(ContextModelInterface::class, class_implements($relatedClass))) {
            /** @var ContextModelInterface $relatedClass */
            return $relatedClass::resolveModels($models);
        }

        return collect($models)->toArray();
    }

    protected function applyContextExclusions(array $data): array
    {
        $exclusions = get_class($this->resource)::getContextExcludes();

        foreach ($exclusions as $excludeKey) {
            unset($data[$excludeKey]);
        }

        return $data;
    }
}
