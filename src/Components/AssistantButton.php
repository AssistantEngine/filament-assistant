<?php

namespace AssistantEngine\Filament\Components;

use App\Filament\Resources\ProductResource;
use AssistantEngine\SDK\AssistantEngine;
use AssistantEngine\SDK\Models\Options\ConversationOption;
use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class AssistantButton extends Component
{
    const ASSISTANT_BUTTON_TRIGGER_MODAL = 'modal';
    const ASSISTANT_BUTTON_TRIGGER_SIDEBAR = 'sidebar';

    const EVENT_OPEN_ASSISTANT = 'assistant-button:open-assistant';
    const EVENT_CLOSE_ASSISTANT = 'assistant-button:close-assistant';

    public $visible = true;

    public $trigger = self::ASSISTANT_BUTTON_TRIGGER_MODAL;

    public $options = [];

    public function boot()
    {
        if ($this->getActiveResource() && $this->getActivePage()) {
            $activeResourceClass = $this->getActiveResource();
            $activePage = $this->getActivePage();

            if (
                ($activeResourceClass && !method_exists($activeResourceClass, "assistant")) ||
                ($activePage && !method_exists($activePage, "openAssistant"))
            ) {
                $this->visible = false;
            }
        }
    }

    public function openAssistant()
    {
        $this->dispatch(self::EVENT_OPEN_ASSISTANT, $this->trigger);
    }

    #[On(AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_OPEN)]
    public function handleSidebarOpen(): void
    {
        $this->visible = false;
    }

    #[On(AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_CLOSE)]
    public function handleSidebarClose(): void
    {
        $this->visible = true;
    }

    public function render()
    {
        return view('filament-assistant::assistant-button');
    }

    public static function isGlobalAssistantAvailable()
    {
        $activeResourceClass = self::getActiveResource();
        $activePage = self::getActivePage();

        if ($activeResourceClass && $activePage) {
            if (
                (method_exists($activeResourceClass, "assistant")) &&
                (method_exists($activePage, "openAssistant"))
            ) {
                return true;
            }
        }

        return false;
    }

    public static function getActiveResource()
    {
        // Find the resource class from the registered resources
        foreach (Filament::getResources() as $key => $resource) {
            if (request()->routeIs($resource::getRouteBaseName() . '.*')) {
                return $resource;
            }
        }

        return null;
    }

    public static function getActivePage()
    {
        /** @var ProductResource $activeResource */
        $activeResource = self::getActiveResource();

        if (!$activeResource) {
            return null;
        }

        // Find the resource class from the registered resources
        foreach ($activeResource::getPages() as $key => $page) {
            $page = $page->getPage();

            if (request()->routeIs($page::getRouteName())) {
                return $page;
            }
        }

        return null;
    }
}
