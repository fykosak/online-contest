<?php

namespace FOL\Modules\GameModule\Presenters;

use App\Model\Authentication\TeamAuthenticator;
use Dibi\Exception;
use LoginFormComponent;
use Nette\Application\AbortException;
use RecoverFormComponent;

/**
 * Class AuthPresenter
 * TODO Take care, this presenter is not intentionally child of GameModule/BasePresenter
 */
class AuthPresenter extends \FOL\Modules\Core\BasePresenter {

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
        $this->redirect(':Game:Game:default');
    }

    public function renderLogin(): void {
        $this->setPagetitle(_('Přihlásit se'));
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->getContext(), $this->authenticator,':Game:Game:default');
    }
}
