<?php

use Nette\Application\UI\Form;
use App\Model\Authentication\TeamAuthenticator;
use App\Model\Interlos;

class PasswordChangeFormComponent extends BaseComponent {

    private function formSubmitted(Form $form): void {
        $values = $form->getValues();
        $changes = [
            'password' => TeamAuthenticator::passwordHash($values['password']),
        ];
        $teamId = $this->getPresenter()->user->getIdentity()->id_team;
        Interlos::teams()->update($changes)->where('[id_team] = %i', $teamId)->execute();

        $this->getPresenter()->flashMessage(_("Heslo bylo změněno."), "info");
        $this->getPresenter()->redirect("Team:default");
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm();

        $form->addPassword("password", "Nové heslo")
            ->addRule(Form::FILLED, "Heslo musí být vyplněno.");

        $form->addPassword("passwordCheck", "Nové heslo (pro kontrolu)")
            ->addRule(Form::EQUAL, "Hesla se neshodují", $form['password'])
            ->setOmitted();

        $form->addSubmit("submit", "Odeslat");
        $form->onSuccess[] = function (Form $form) {
            $this->formSubmitted($form);
        };

        return $form;
    }

}
