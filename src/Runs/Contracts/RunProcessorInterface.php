<?php

namespace AssistantEngine\Filament\Runs\Contracts;

use AssistantEngine\Filament\Runs\Models\Run;

interface RunProcessorInterface
{
    public function process(Run $run): void;
}
