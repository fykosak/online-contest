<?php

namespace FOL\Components\NotificationForm;

use FOL\Components\BaseForm;
use FOL\Model\ORM\Services\ServiceNotification;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Http\Response;
use FOL\Components\BaseComponent;

class NotificationFormComponent extends BaseComponent {

    protected ServiceNotification $serviceNotification;

    public function injectNotificationService(ServiceNotification $serviceNotification): void {
        $this->serviceNotification = $serviceNotification;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    private function formSucceeded(Form $form): void {
        if (!$this->getPresenter()->user->isAllowed('noticeboard', 'add')) {
            $this->getPresenter()->error('Nemáte oprávnění pro přidání notifikace.', Response::S403_FORBIDDEN);
        }

        $values = $form->getValues();
        $this->serviceNotification->createNewModel(
            [
                'message' => $values['message'],
                'lang' => $this->translator->getSupportedLanguages()[$values['lang']],
            ]
        );
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
