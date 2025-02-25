<?php

namespace AssistantEngine\Filament\Chat\Resolvers;

use AssistantEngine\Filament\Chat\Contracts\ContextModelInterface;
use AssistantEngine\Filament\Chat\Contracts\ContextResolverInterface;
use Filament\Pages\Page;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationManager;

class ContextResolver implements ContextResolverInterface
{
    public function resolve(Page $page): array
    {
        $result = [];

        // Collect models directly related to the page's record
        if (isset($page->record)) {
            $this->collectFromRecord($result, $page->record);
        }

        // Collect models for ListRecords page
        if ($page instanceof ListRecords) {
            $this->collectFromListRecordsPage($result, $page);
        }

        // Collect models for ManageRelatedRecords page
        if ($page instanceof ManageRelatedRecords) {
            $this->collectFromManageRelatedRecordsPage($result, $page);
        }

        // Collect models from relation managers
        if (method_exists($page, "getRelationManagers") && !empty($page->getRelationManagers())) {
            $this->collectFromRelationManagers($result, $page);
        }

        return $this->resolveCollectedModels($result);
    }

    protected function collectFromRecord(array &$result, $record): void
    {
        $relatedClass = get_class($record);
        $relatedModels = [$record];
        $this->collectRelatedModels($result, $relatedClass, $relatedModels);
    }

    protected function collectFromListRecordsPage(array &$result, ListRecords $page): void
    {
        $relatedClass = (get_class($page)::getResource())::getModel();
        $relatedModels = $page->getTable()->getRecords()->all();

        if ($relatedModels) {
            $this->collectRelatedModels($result, $relatedClass, $relatedModels);
        }
    }

    protected function collectFromManageRelatedRecordsPage(array &$result, ManageRelatedRecords $page): void
    {
        $relationship = $page->getRelationship();
        $relatedClass = get_class($relationship->getRelated());
        $relatedModels = $relationship->get()->all();

        if ($relatedModels) {
            $this->collectRelatedModels($result, $relatedClass, $relatedModels);
        }
    }

    protected function collectFromRelationManagers(array &$result, Page $page): void
    {
        foreach ($page->getRelationManagers() as $className) {
            /** @var RelationManager $className */
            $relationName = $className::getRelationshipName();
            $relationship = $page->record->{$relationName}();
            $relatedClass = get_class($relationship->getRelated());
            $relatedModels = $relationship->get()->all();

            if ($relatedModels) {
                $this->collectRelatedModels($result, $relatedClass, $relatedModels);
            }
        }
    }

    protected function collectRelatedModels(array &$result, string $relatedClass, array $relatedModels): void
    {
        if (isset($result[$relatedClass])) {
            $result[$relatedClass] = array_merge($result[$relatedClass], $relatedModels);
        } else {
            $result[$relatedClass] = $relatedModels;
        }
    }

    protected function resolveCollectedModels(array $result): array
    {
        foreach ($result as $relatedClass => $models) {
            if (in_array(ContextModelInterface::class, class_implements($relatedClass))) {
                $result[$relatedClass] = $relatedClass::resolveModels($models);
            } else {
                $result[$relatedClass] = collect($models)->toArray();
            }
        }
        return $result;
    }
}
