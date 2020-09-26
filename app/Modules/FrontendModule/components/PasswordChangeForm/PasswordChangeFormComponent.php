<?php

use FOL\Model\ORM\TeamsService;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use App\Model\Authentication\TeamAuthenticator;

class PasswordChangeFormComponent extends BaseComponent {

    protected TeamsService $teamsService;

    public function injectTeamsService(TeamsService $teamsService): void {
        $this->teamsService = $teamsService;
    }

    /**
     * @param Form $form
     * @return void
     * @throws \Dibi\Exception
     * @throws AbortException
     */
    private function formSubmitted(Form $form): void {
        $values = $form->getValues();
        $changes = [
            'password' => TeamAuthenticator::passwordHash($values['password']),
        ];
        $teamId = $this->getPresenter()->user->getIdentity()->id_team;
        $this->teamsService->update($changes)->where('[id_team] = %i', $teamId)->execute();

        $this->getPresenter()->flashMessage(_("Heslo bylo změněno."), "info");
        $this->getPresenter()->redirect("Team:default");
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());

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
