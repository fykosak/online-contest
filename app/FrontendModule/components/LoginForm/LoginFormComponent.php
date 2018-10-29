<?php

use Nette\Application\UI\Form,
    Nette\ComponentModel\IContainer,
    Nette\Security,
    App\Model\Authentication\AbstractAuthenticator;
use App\Model\Interlos;

class LoginFormComponent extends BaseComponent
{
    /** @var App\Model\Authentication\AbstractAuthenticator */
    private $authenticator;
            
    public function __construct(AbstractAuthenticator $authenticator, IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->authenticator = $authenticator;
    }

    public function formSubmitted(Form $form) {
	$values = $form->getValues();

	try {
	    //$this->getPresenter()->user->login($values['name'], $values['password']);
            $this->authenticator->login($values['name'], $values['password']);
	}
	catch(Security\AuthenticationException $e) {
	    if ($e->getCode() == Security\IAuthenticator::IDENTITY_NOT_FOUND) {
		$this->getPresenter()->flashMessage(_("Daný tým neexistuje."), "danger");
	    }
	    else {
		$this->getPresenter()->flashMessage(_("Nesprávné heslo"), "danger");
	    }
	    return;
	}
	catch(Exception $e) {
	    $this->getPresenter()->flashMessage(_("Stala se neočekávaná chyba."), "danger");
	    Debug::processException($e);
	    return;
	}

		if (Interlos::isGameActive()) {
			$this->getPresenter()->redirect("Game:default");
		}
		else {
			$this->getPresenter()->redirect("Team:default");
		}
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm($name) {
	$form = new BaseForm($this, $name);

	$form->addText("name", "Název týmu")
	    ->addRule(Form::FILLED, "Název týmu musí být vyplněn.");

	$form->addPassword("password", "Heslo")
	    ->addRule(Form::FILLED, "Heslo musí být vyplněno.");

	$form->addSubmit("login", "Přihlásit se");
	$form->onSuccess[] = array($this, "formSubmitted");

	return $form;
    }

}
