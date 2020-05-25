<?php

namespace Backend\Modules\Compression\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Model;
use Backend\Modules\Compression\Clients\TinyClient;
use Backend\Modules\Compression\Domain\Settings\Command\SaveSettings;
use Backend\Modules\Compression\Domain\Settings\Event\SettingsSavedEvent;

/**
 * Class Ping
 * @package Backend\Modules\Compression\Actions
 */
final class Ping extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();

        // Successful API connection
        if ($this->ping()) {
            $this->redirect($this->getLink(['report' => 'correct-api-key']));
        }

        // Unsuccessful API connection
        $this->resetCompressionEngineConnection();
        $this->redirect($this->getLink(['error' => 'invalid-api-key']));
    }

    private function ping(): bool
    {
        $client = TinyClient::createFromModuleSettings($this->get('fork.settings'));
        return $client->isValidApiKey();
    }

    private function getLink(array $parameters = []): string
    {
        return Model::createUrlForAction('Settings', $this->getModule(), null, $parameters);
    }

    private function resetCompressionEngineConnection(): void
    {
        $saveSettings = new SaveSettings($this->get('fork.settings'));
        $saveSettings->apiKey = null;

        $this->get('command_bus')->handle($saveSettings);

        $this->get('event_dispatcher')->dispatch(
            SettingsSavedEvent::EVENT_NAME,
            new SettingsSavedEvent($saveSettings)
        );
    }
}
