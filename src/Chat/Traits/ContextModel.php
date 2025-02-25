<?php

namespace AssistantEngine\Filament\Chat\Traits;

use AssistantEngine\Filament\Chat\Resolvers\ContextModelResolver;

trait ContextModel
{
    public static function getContextMetaData(): array
    {
        return [
            'schema' => self::class
        ];
    }

    public static function getContextExcludes(): array
    {
        return [];
    }

    public static function resolveModels(array $models): array
    {
        $result = [];
        $result['data'] = null;

        if (count($models) > 0) {
            $result['data'] = ContextModelResolver::collection($models)->resolve();
        }

        $result['meta'] = self::getContextMetaData();

        return $result;
    }
}
