<?php

namespace FOL\Modules\PublicModule\Presenters;

use App\Model\Authentication\TeamAuthenticator;
use Dibi\Exception;
use LoginFormComponent;
use Nette\Application\AbortException;
use RecoverFormComponent;

class AuthPresenter extends BasePresenter {

    protected TeamAuthenticator $authenticator;

    public function injectSecondary(TeamAuthenticator $authenticator): void {
        $this->authenticator = $authenticator;
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function actionLogout(): void {
        $this->getUser()->logout();
        $this->redirect(':Public:Default:default');
    }

    public function renderLogin(): void {
        $this->setPagetitle(_('Přihlásit se'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws AbortException
     */
    public function renderRecover(): void {
        $this->setPageTitle(_('Obnova hesla'));
        if (!$this->yearsService->isGameMigrated()) {
            $this->flashMessage(_('Změnu hesla proveďte editací vaší přihlášky.'), 'danger');
            $this->redirect(':Public:Default:default');
        }
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->getContext(), $this->authenticator);
    }

    protected function createComponentRecover(): RecoverFormComponent {
        return new RecoverFormComponent($this->getContext());
    }
}
