<?php

namespace Backend\Modules\Compression\Domain\Settings\Command;

use Common\ModulesSettings;

final class SaveSettingsHandler
{
    /**
     * @var ModulesSettings
     */
    private $modulesSettings;

    public function __construct(ModulesSettings $modulesSettings)
    {
        $this->modulesSettings = $modulesSettings;
    }

    public function handle(SaveSettings $settings): void
    {
        $this->modulesSettings->set('Compression', 'api_key', $settings->apiKey);
    }
}
