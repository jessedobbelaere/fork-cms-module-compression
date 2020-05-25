<?php

namespace Backend\Modules\Compression\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Engine\Model;
use Backend\Modules\Compression\Clients\TinyClient;
use Backend\Modules\Compression\Domain\Settings\Command\SaveSettings;
use Backend\Modules\Compression\Domain\Settings\Event\SettingsSavedEvent;
use Backend\Modules\Compression\Domain\Settings\SettingsType;
use Symfony\Component\Form\Form;

final class Settings extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $client = TinyClient::createFromModuleSettings($this->get('fork.settings'));
            $this->template->assign('form', $form->createView());
            $this->template->assign('monthlyCompressionCount', $client->getMonthlyCompressionCount());

            $this->parse();
            $this->display();

            return;
        }

        $settings = $this->saveSettings($form);
        $this->get('event_dispatcher')->dispatch(SettingsSavedEvent::EVENT_NAME, new SettingsSavedEvent($settings));

        $this->redirect(
            Model::createUrlForAction(
                'Ping',
                $this->getModule(),
                null,
                [
                    'report' => 'saved',
                ]
            )
        );
    }

    private function getForm(): Form
    {
        $form = $this->createForm(
            SettingsType::class,
            new SaveSettings($this->get('fork.settings'))
        );

        $form->handleRequest($this->getRequest());
        return $form;
    }

    private function saveSettings(Form $form): SaveSettings
    {
        /** @var SaveSettings $settings */
        $settings = $form->getData();
        $this->get('command_bus')->handle($settings);

        return $settings;
    }
}
