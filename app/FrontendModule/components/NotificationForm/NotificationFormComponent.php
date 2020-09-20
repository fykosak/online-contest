<?php

use Nette\Application\UI\Form;
use App\Model\NotificationModel;

class NotificationFormComponent extends BaseComponent {

    private NotificationModel $notificationModel;

    public function __construct(NotificationModel $notificationModel) {
        parent::__construct();
        $this->notificationModel = $notificationModel;
    }

    private function formSucceeded(Form $form): void {
        if (!$this->getPresenter()->user->isAllowed('noticeboard', 'add')) {
            $this->getPresenter()->error('Nemáte oprávnění pro přidání notifikace.', Nette\Http\Response::S403_FORBIDDEN);
        }

        $values = $form->getValues();
        $this->notificationModel->insertNotification($values['messageCs'], $values['messageEn']);
        $this->getPresenter()->flashMessage(_("Notifikace byla vložena"), "info");
        $this->getPresenter()->redirect('Noticeboard:add');
    }

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm();
        $form->addText("messageCs", "Zpráva v češtině.")
            ->addRule(Form::FILLED, "Zpráva v češtině musí být vyplněna.");
        $form->addText("messageEn", "Zpráva v angličtině.")
            ->addRule(Form::FILLED, "Zpráva v angličtině musí být vyplněna.");
        $form->addSubmit("submit", "Odeslat");
        $form->onSuccess[] = function (Form $form) {
            $this->formSucceeded($form);
        };
        return $form;
    }
}
