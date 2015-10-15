<?php

use Nette\Application\UI\Form,
    Nette\ComponentModel\IContainer,
    App\Model\NotificationModel;

class NotificationFormComponent extends BaseComponent
{
    /** @var NotificationModel */
    private $notificationModel;

    public function __construct(NotificationModel $notificationModel, IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->notificationModel = $notificationModel;
    }


    public function formSucceeded(Form $form) {
        if(!$this->getPresenter()->user->isAllowed('noticeboard', 'add')) {
            $this->getPresenter()->error('Nemáte oprávnění pro přidání notifikace.', Nette\Http\Response::S403_FORBIDDEN);
        }
        
        $values = $form->getValues();
        $this->notificationModel->insertNotification($values['messageCs'], $values['messageEn']);
        $this->getPresenter()->flashMessage(_("Notifikace byla vložena"), "info");
        $this->getPresenter()->redirect('Noticeboard:add');
    }
    
    protected function createComponentForm($name) {
        $form = new BaseForm($this, $name);
        $form->addText("messageCs", "Zpráva v češtině.")
                ->addRule(Form::FILLED, "Zpráva v češtině musí být vyplněna.");
        $form->addText("messageEn", "Zpráva v angličtině.")
                ->addRule(Form::FILLED, "Zpráva v angličtině musí být vyplněna.");
        $form->addSubmit("submit", "Odeslat");
        $form->onSuccess[] = array($this, "formSucceeded");
        
        return $form;
    }
}