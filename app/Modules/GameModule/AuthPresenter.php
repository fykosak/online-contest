<?php

namespace FOL\Modules\GameModule;

use FOL\Model\Authentication\TeamAuthenticator;
use FOL\Components\LoginForm\LoginFormComponent;
use FOL\Components\RecoverForm\RecoverFormComponent;
use Nette\Application\AbortException;

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
            $this->redirect(':Public:Default:default');
        }
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->getContext(), $this->authenticator, ':Game:Task:default');
    }

    protected function createComponentRecover(): RecoverFormComponent {
        return new RecoverFormComponent($this->getContext());
    }
}
