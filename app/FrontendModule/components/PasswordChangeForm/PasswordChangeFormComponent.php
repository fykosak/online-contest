<?php

use Nette\Application\UI\Form;
use App\Model\Authentication\TeamAuthenticator;
use App\Model\Interlos;

class PasswordChangeFormComponent extends BaseComponent
{
    public function formSubmitted(Form $form) {
        $values = $form->getValues();
        $changes = [
            'password' => TeamAuthenticator::passwordHash($values['password'])
        ];
        $teamId = $this->getPresenter()->user->getIdentity()->id_team;
        Interlos::teams()->update($changes)->where('[id_team] = %i', $teamId)->execute();
        
        $this->getPresenter()->flashMessage(_("Heslo bylo změněno."), "info");
	    $this->getPresenter()->redirect("Team:default");
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm($name) {
	    $form = new BaseForm($this, $name);

	    $form->addPassword("password", "Nové heslo")
            ->addRule(Form::FILLED, "Heslo musí být vyplněno.");
        
        $form->addPassword("passwordCheck", "Nové heslo (pro kontrolu)")
            ->addRule(Form::EQUAL, "Hesla se neshodují", $form['password'])
            ->setOmitted();

	    $form->addSubmit("submit", "Odeslat");
	    $form->onSuccess[] = array($this, "formSubmitted");

	    return $form;
    }

}
