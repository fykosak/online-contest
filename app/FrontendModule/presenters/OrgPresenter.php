<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos;

class OrgPresenter extends BasePresenter {
    
    /** @var \App\Model\Authentication\OrgAuthenticator @inject*/
    public $authenticator;
    
    public function actionDefault() {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
    }
    
    public function renderLogin() {
	$this->setPagetitle(_("Přihlásit se"));
    }
    
    protected function createComponentLogin($name) {
	return new \LoginFormComponent($this->authenticator, $this, $name);
    }
}