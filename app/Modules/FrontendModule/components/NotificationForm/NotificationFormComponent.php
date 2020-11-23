<?php

use App\Model\Translator\GettextTranslator;
use FOL\Model\ORM\NotificationService;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Http\Response;

class NotificationFormComponent extends BaseComponent {

    protected NotificationService $notificationModel;

    public function injectNotificationService(NotificationService $notificationModel): void {
        $this->notificationModel = $notificationModel;
    }

    /**
     * @param Form $form
     * @return void
     * @throws \Dibi\Exception
     * @throws AbortException
     * @throws BadRequestException
     */
    private function formSucceeded(Form $form): void {
        if (!$this->getPresenter()->user->isAllowed('noticeboard', 'add')) {
            $this->getPresenter()->error('Nemáte oprávnění pro přidání notifikace.', Response::S403_FORBIDDEN);
        }

        $values = $form->getValues();
        $this->notificationModel->insert($values['message'], GettextTranslator::getSupportedLangs()[$values['lang']]);
        $this->getPresenter()->flashMessage(_('Notifikace byla vložena'), 'info');
        $this->getPresenter()->redirect('Noticeboard:add');
    }

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());
        $form->addText('message', _('Message'))
            ->setRequired(true);
        $form->addSelect('lang', _('Lang'), GettextTranslator::getSupportedLangs())
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
