<?php

namespace AssistantEngine\Filament\Traits;

use AssistantEngine\SDK\Models\Options\ConversationOption;
use Filament\Pages\Page;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationManager;

trait AssistedResource
{
    public static string $modelsContextKey = 'models';

    public static function prepareConversationOption(Page $page, string $assistantKey, $additionalContextModels = []): ConversationOption
    {
        $option = new ConversationOption($assistantKey);
        $option->title = $page->getTitle() ?? null;
        $option->context = self::getAssistantContext($page, $additionalContextModels);

        /**
         * @var HasAssistant $page
         */
        if (method_exists($page, 'getAssistantSubjectKey') && $page->getAssistantSubjectKey()) {
            $option->subject_id = $page->getAssistantSubjectKey();
        }

        return $option;
    }

    public static function getAssistantContext(Page $page, $additionalContextModels = []): array
    {
        /**
         * @var HasAssistant $page
         */
        $result = [];
        $result[self::$modelsContextKey] = self::loadVisibleModelsByPage($page, $additionalContextModels);

        if (method_exists($page, 'getAssistantPageDescription') && $page->getAssistantPageDescription()) {
            $result[$page->pageDescriptionContextKey ?? 'pageDescription'] = $page->getAssistantPageDescription();
        }

        return $result;
    }

    public static function loadVisibleModelsByPage(Page $page, $additionalContextModels = []): array
    {
        /**
         * @var AssistantContext $relatedClass
         */
        $result = [];

        if (isset($page->record)) {
            $record = $page->record;
            $relatedClass = get_class($record);
            $relatedModels = [$record];
            self::collectRelatedModels($result, $relatedClass, $relatedModels);
        }

        if ($page instanceof ManageRelatedRecords) {
            $relationship = $page->getRelationship();
            $relatedClass = get_class($relationship->getRelated());
            $relatedModels = $relationship->get()->all();

            self::collectRelatedModels($result, $relatedClass, $relatedModels);
        }

        if (method_exists($page, "getRelationManagers") && !empty($page->getRelationManagers())) {
            foreach ($page->getRelationManagers() as $className) {
                /** @var RelationManager $className */
                $relationName = $className::getRelationshipName();
                $relationship = $page->record->{$relationName}();
                $relatedClass = get_class($relationship->getRelated());
                $relatedModels = $relationship->get()->all();

                self::collectRelatedModels($result, $relatedClass, $relatedModels);
            }
        }

        if (method_exists($page, 'getAdditionalAssistantContext')) {
            /**
             * @var HasAssistant $page
             */
            foreach ($page->getAdditionalAssistantContextModels() as $relatedClass => $models) {
                self::collectRelatedModels($result, $relatedClass, $models);
            }
        }

        foreach ($additionalContextModels as $relatedClass => $models) {
            self::collectRelatedModels($result, $relatedClass, $models);
        }

        // After collecting all models, resolve them
        foreach ($result as $relatedClass => $models) {
            $result[$relatedClass] = $relatedClass::resolveContextModels($models);
        }

        return $result;
    }

    protected static function collectRelatedModels(array &$result, string $relatedClass, array $relatedModels)
    {
        if (!in_array(AssistantContext::class, class_uses($relatedClass))) {
            throw new \Exception("$relatedClass must implement the AssistantContext trait to be able to resolve as context.");
        }

        if (isset($result[$relatedClass])) {
            $result[$relatedClass] = array_merge($result[$relatedClass], $relatedModels);
        } else {
            $result[$relatedClass] = $relatedModels;
        }
    }

    abstract public static function assistant(Page $page): ConversationOption;
}
