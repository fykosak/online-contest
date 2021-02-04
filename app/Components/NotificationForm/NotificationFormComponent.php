<?php

namespace FOL\Components\NotificationForm;

use FOL\Components\BaseForm;
use FOL\Model\ORM\Services\ServiceNotification;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use FOL\Components\BaseComponent;

final class NotificationFormComponent extends BaseComponent {

    protected ServiceNotification $serviceNotification;

    public function injectNotificationService(ServiceNotification $serviceNotification): void {
        $this->serviceNotification = $serviceNotification;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    private function formSucceeded(Form $form): void {
        $values = $form->getValues();
        $this->serviceNotification->createNewModel([
            'message' => $values['message'],
            'lang' => $this->translator->getSupportedLanguages()[$values['lang']],
        ]);
        $this->getPresenter()->flashMessage(_('Notifikace byla vložena'));
        $this->getPresenter()->redirect('Noticeboard:add');
    }

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());
        $form->addText('message', _('Message'))
            ->setRequired(true);
        $form->addSelect('lang', _('Lang'), $this->translator->getSupportedLanguages())
            ->setRequired(true);
        $form->addSubmit('submit', _('Create'));
        $form->onSuccess[] = function (Form $form) {
            $this->formSucceeded($form);
        };
        return $form;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'notificationForm.latte');
        parent::render();
    }
}
