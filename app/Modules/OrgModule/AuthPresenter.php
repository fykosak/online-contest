<?php

namespace FOL\Modules\OrgModule;

use FOL\Components\LoginForm\LoginFormComponent;
use FOL\Model\Authentication\OrgAuthenticator;

class AuthPresenter extends \FOL\Modules\Core\BasePresenter {

    protected OrgAuthenticator $authenticator;

    public function injectSecondary(OrgAuthenticator $authenticator): void {
        $this->authenticator = $authenticator;
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->getContext(), $this->authenticator, ':Org:Default:default');
    }

    public function renderLogin(): void {
        $this->setPageTitle(_('Přihlásit se'));
    }
}
