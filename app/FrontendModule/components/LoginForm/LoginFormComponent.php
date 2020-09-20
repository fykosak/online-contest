<?php

use Nette\Application\UI\Form;
use Nette\Security;
use App\Model\Authentication\AbstractAuthenticator;
use App\Model\Interlos;
use Tracy\Debugger;

class LoginFormComponent extends BaseComponent {

    private AbstractAuthenticator $authenticator;

    public function __construct(AbstractAuthenticator $authenticator) {
        parent::__construct();
        $this->authenticator = $authenticator;
    }

    private function formSubmitted(Form $form): void {
        $values = $form->getValues();

        try {
            //$this->getPresenter()->user->login($values['name'], $values['password']);
            $this->authenticator->login($values['name'], $values['password']);
        } catch (Security\AuthenticationException $e) {
            if ($e->getCode() == Security\IAuthenticator::IDENTITY_NOT_FOUND) {
                $this->getPresenter()->flashMessage(_("Daný tým neexistuje."), "danger");
            } else {
                $this->getPresenter()->flashMessage(_("Nesprávné heslo"), "danger");
            }
            return;
        } catch (Exception $e) {
            $this->getPresenter()->flashMessage(_("Stala se neočekávaná chyba."), "danger");
            Debugger::exceptionHandler($e); // TODO WTF?
            return;
        }

        if (Interlos::isGameActive()) {
            $this->getPresenter()->redirect("Game:default");
        } else {
            $this->getPresenter()->redirect("Team:default");
        }
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm();

        $form->addText("name", "Název týmu")
            ->addRule(Form::FILLED, "Název týmu musí být vyplněn.");

        $form->addPassword("password", "Heslo")
            ->addRule(Form::FILLED, "Heslo musí být vyplněno.");

        $form->addSubmit("login", "Přihlásit se");
        $form->onSuccess[] = function (Form $form) {
            $this->formSubmitted($form);
        };

        return $form;
    }

}
