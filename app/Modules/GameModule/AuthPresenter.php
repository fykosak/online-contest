<?php

namespace FOL\Modules\GameModule;

use FOL\Components\PasswordChangeForm\PasswordChangeFormComponent;
use FOL\Model\Authentication\TeamAuthenticator;
use FOL\Components\LoginForm\LoginFormComponent;
use FOL\Components\RecoverForm\RecoverFormComponent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Security\AuthenticationException;

/**
 * Class AuthPresenter
 * @note Take care thi presenter is not child of GameModule/BasePresenter
 */
final class AuthPresenter extends \FOL\Modules\Core\BasePresenter {

    private TeamAuthenticator $authenticator;

    public function injectSecondary(TeamAuthenticator $authenticator): void {
        $this->authenticator = $authenticator;
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function actionLogout(): void {
        $this->getUser()->logout();
        $this->redirect(':Game:Auth:login');
    }

    public function renderLogin(): void {
        $this->setPageTitle(_('Přihlásit se'));
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function renderRecover(): void {
        $this->setPageTitle(_('Obnova hesla'));
        if (!$this->gameSetup->isGameMigrated) {
            $this->flashMessage(_('Změnu hesla proveďte editací vaší přihlášky.'), 'danger');
            $this->redirect(':Game:Auth:login');
        }
    }

    /**
     * @param string|null $token
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderChangePassword(?string $token = null): void {
        if (!is_null($token)) {
            try {
                $this->authenticator->authenticateByToken($token);
            } catch (AuthenticationException $e) {
                if (!$this->user->isAllowed('team', 'edit')) {
                    $this->flashMessage(_('Heslo pro tým již zřejmě bylo obnoveno jiným členem týmu.'), 'danger');
                    $this->redirect(':Game:Auth:login');
                }
            }
        }
        if (!$this->user->isAllowed('team', 'edit')) {
            $this->flashMessage(_('Nejprve se prosím přihlaste.'), 'danger');
            $this->redirect(':Game:Auth:login');
        }

        $this->setPageTitle(_('Změna hesla'));
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->getContext(), $this->authenticator, ':Game:Task:default');
    }

    protected function createComponentRecover(): RecoverFormComponent {
        return new RecoverFormComponent($this->getContext());
    }

    protected function createComponentPasswordChangeForm(): PasswordChangeFormComponent {
        return new PasswordChangeFormComponent($this->getContext(), $this->getLoggedTeam());
    }
}
