<?php

namespace AssistantEngine\Filament\Resolvers;


use AssistantEngine\Filament\Traits\AssistantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContextModelResolver extends JsonResource
{
    public function toArray(Request $request): ?array
    {
        if (in_array(AssistantContext::class, class_uses($this->resource), true) === false) {
            return null;
        }

        $relations = $this->resource->getRelations();
        $this->resource->unsetRelations();

        $data = parent::toArray($request);

        foreach ($relations as $relationName => $models) {
            $relatedObj = $this->resource->$relationName()->getRelated();
            $relatedObjClass = get_class($relatedObj);

            if (in_array(AssistantContext::class, class_uses($relatedObjClass), true)) {
                if (!is_iterable($models)) {
                    $models = [$models];
                }

                $data[$relationName] = $relatedObjClass::resolveContextModels($models);
            }
        }

        foreach ($this->resource->getContextExclude() as $excludeKey) {
            unset($data[$excludeKey]);
        }

        return $data;
    }
}
