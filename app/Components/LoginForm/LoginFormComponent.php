<?php

namespace FOL\Components\LoginForm;

use Exception;
use FOL\Model\Authentication\AbstractAuthenticator;
use FOL\Components\BaseForm;
use FOL\Model\GameSetup;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Tracy\Debugger;
use FOL\Components\BaseComponent;

final class LoginFormComponent extends BaseComponent {

    private AbstractAuthenticator $authenticator;
    private string $redirectDestination;
    private GameSetup $gameSetup;

    public function __construct(Container $container, AbstractAuthenticator $authenticator, string $redirectDestination) {
        parent::__construct($container);
        $this->authenticator = $authenticator;
        $this->redirectDestination = $redirectDestination;
    }

    public function injectGameSetup(GameSetup $gameSetup): void {
        $this->gameSetup = $gameSetup;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    private function formSubmitted(Form $form): void {
        $values = $form->getValues();

        try {
            $this->authenticator->login($values['name'], $values['password']);
        } catch (AuthenticationException $e) {
            if ($e->getCode() == Authenticator::IDENTITY_NOT_FOUND) {
                $this->getPresenter()->flashMessage(_('Daný tým neexistuje.'), 'danger');
            } else {
                $this->getPresenter()->flashMessage(_('Nesprávné heslo'), 'danger');
            }
            return;
        } catch (Exception $e) {
            $this->getPresenter()->flashMessage(_('Stala se neočekávaná chyba.'), 'danger');
            Debugger::log($e);
            return;
        }

        $this->getPresenter()->redirect($this->redirectDestination);
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());

        $form->addText('name', 'Název týmu')
            ->addRule(Form::FILLED, 'Název týmu musí být vyplněn.');

        $form->addPassword('password', 'Heslo')
            ->addRule(Form::FILLED, 'Heslo musí být vyplněno.');

        $form->addSubmit('login', 'Přihlásit se');
        $form->onSuccess[] = function (Form $form) {
            $this->formSubmitted($form);
        };

        return $form;
    }

    public function render(): void {
        $this->template->isMigrated = $this->gameSetup->isGameMigrated;
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'loginForm.latte');
        parent::render();
    }
}
