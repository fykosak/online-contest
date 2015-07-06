<?php

namespace App\FrontendModule\Presenters;

use Nette,
    App\Model\Interlos;

class DefaultPresenter extends BasePresenter {

	public function actionLogout() {
                $this->getUser()->logout();
		$this->redirect("default");
	}

	public function renderChat() {
		$this->getComponent("chat")->setSource(Interlos::chat()->findAll($this->lang));
		$this->setPageTitle(_("Diskuse"));
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

	public function renderRules() {
		$this->setPagetitle(_("Pravidla"));
                $this->changeViewByLang();                
	}

	public function renderTaskExamples() {
		$this->setPagetitle(_("Rozcvička"));
	}

	// ----- PROTECTED METHODS
        
        protected function createComponentChat($name) {
		$chat = new \ChatListComponent($this, $name);
		return $chat;
	}
	
	protected function createComponentLogin($name) {
		return new \LoginFormComponent($this, $name);
	}

}
