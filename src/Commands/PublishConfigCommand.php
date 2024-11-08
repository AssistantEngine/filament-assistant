<?php

namespace AssistantEngine\Filament\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PublishConfigCommand extends Command
{
    protected $signature = 'filament-assistant:publish-config';

    protected $description = 'Publish and merge configuration files for Filament Assistant';

    public function handle()
    {
        $configPath = config_path('assistant-engine.php');

        // Step 1: Check if the config file exists
        if (!File::exists($configPath)) {
            // Step 2: Publish the Laravel Assistant config
            $this->info('Publishing Laravel Assistant configuration...');
            Artisan::call('vendor:publish', [
                '--tag' => 'assistant-config',
            ]);
            $this->info('Laravel Assistant configuration published.');
        }

        if (config('assistant-engine.filament-assistant', false)) {
            $this->info('Filament Config already present');

            return;
        }

        // Step 3: Read the existing assistant-engine.php config as a string
        $this->info('Reading existing assistant-engine.php configuration...');
        $assistantEngineConfigContent = file_get_contents($configPath);

        // Step 4: Load the Filament Assistant default configurations as a string
        $this->info('Loading Filament Assistant default configurations...');
        $filamentAssistantConfigContent = file_get_contents($this->getFilamentAssistantConfig());

        // Step 5: Extract array strings
        $assistantEngineArrayString = $this->extractArrayString($assistantEngineConfigContent);
        $filamentAssistantArrayString = $this->extractArrayString($filamentAssistantConfigContent);

        // Step 6: Merge the configurations at the string level
        $this->info('Merging configurations...');
        $mergedArrayString = $this->mergeArrayStrings($assistantEngineArrayString, $filamentAssistantArrayString);

        // Step 7: Construct the new configuration file content
        $newConfigContent = "<?php\n\nreturn " . $mergedArrayString . "\n";

        // Step 8: Write the merged configuration back to assistant-engine.php
        $this->info('Writing merged configuration to assistant-engine.php...');
        file_put_contents($configPath, $newConfigContent);

        $this->info('Configuration published and merged successfully.');
    }

    protected function getFilamentAssistantConfig()
    {
        return __DIR__ . '/../../config/filament-assistant.php';
    }

    protected function extractArrayString($configContent)
    {
        $pattern = '/return\s*(\[.*\]);/s';
        if (preg_match($pattern, $configContent, $matches)) {
            return $matches[1]; // The array content
        }
        return '[]'; // Return an empty array if not found
    }

    protected function mergeArrayStrings($assistantEngineArrayString, $filamentAssistantArrayString)
    {
        // Remove the last closing bracket "]" and any whitespace before it
        $assistantEngineConfigContent = rtrim($assistantEngineArrayString);
        $lastBracketPosition = strrpos($assistantEngineConfigContent, ']');

        if ($lastBracketPosition === false) {
            // Handle error: No closing bracket found
            $this->error('Invalid assistant-engine.php configuration file.');
            return null;
        }

        // Split the content at the last closing bracket
        $assistantEngineArrayString = rtrim(substr($assistantEngineConfigContent, 0, $lastBracketPosition), PHP_EOL . ',') . ',';

        $filamentAssistantArrayString = trim($filamentAssistantArrayString, "[\n");
        $filamentAssistantArrayString = substr($filamentAssistantArrayString, 0, strrpos($filamentAssistantArrayString, ']'));

        // Check if Assistant Engine array is empty
        if (trim($assistantEngineArrayString) === '[') {
            // Assistant Engine array is empty
            $mergedArrayString = '[' . $filamentAssistantArrayString . "\n];";
        } else {
            // Assistant Engine array has existing content
            $mergedArrayString = $assistantEngineArrayString . PHP_EOL . $filamentAssistantArrayString . "\n];";
        }

        return $mergedArrayString;
    }
}
