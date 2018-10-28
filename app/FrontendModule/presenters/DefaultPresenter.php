<?php

namespace App\FrontendModule\Presenters;

use Nette,
    Nette\Mail\IMailer,
    App\Model\Interlos;

class DefaultPresenter extends BasePresenter {
    
        /** @var \App\Model\Authentication\TeamAuthenticator @inject*/
	public $authenticator;
	
	/** @var  IMailer @inject*/
	public $mailer;

	public function actionLogout() {
                $this->getUser()->logout();
		$this->redirect("default");
	}

	public function renderChat() {
		$this->getComponent("chat")->setSource(Interlos::chat()->findAll($this->lang));
		$this->setPageTitle(_("Diskuse (česká verze)"));
	}

	public function renderDefault() {
		$this->setPagetitle(_("Mezinárodní soutež ve fyzice"));
                $this->template->year = Interlos::getCurrentYear();
                $this->changeViewByLang();
	}

	public function renderLastYears() {
		$this->setPagetitle(_("Minulé ročníky"));
                $this->changeViewByLang();                
	}

	public function renderLogin() {
		$this->setPagetitle(_("Přihlásit se"));
	}
        
        public function renderRecover() {
                $this->setPageTitle(_("Obnova hesla"));
        }

	public function renderRules() {
		$this->setPagetitle(_("Pravidla"));
                $this->changeViewByLang();                
	}
        
        public function renderFaq() {
            $this->setPagetitle(_("FAQ"));
            $this->changeViewByLang();
        }

	public function renderTaskExamples() {
		$this->setPagetitle(_("Rozcvička"));
	}

	public function renderOtherEvents() {
		$this->setPagetitle(_("Další soutěže"));
		$this->changeViewByLang();
	}

	// ----- PROTECTED METHODS
        
        protected function createComponentChat($name) {
		$chat = new \ChatListComponent($this, $name);
		return $chat;
	}
	
	protected function createComponentLogin($name) {
		return new \LoginFormComponent($this->authenticator, $this, $name);
	}
        
        protected function createComponentRecover($name) {
		return new \RecoverFormComponent($this->authenticator, $this->mailer, $this, $name);
	}

}
