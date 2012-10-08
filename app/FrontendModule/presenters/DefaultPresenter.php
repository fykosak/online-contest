<?php
class Frontend_DefaultPresenter extends Frontend_BasePresenter {

	public function actionLogout() {
		Environment::getUser()->logout();
		$this->redirect("default");
	}

	public function renderChat() {
		$this->getComponent("chat")->setSource(Interlos::chat()->findAll());
		$this->setPageTitle(_("Diskuse"));
	}

	public function renderDefault() {
		$this->setPagetitle(_("Mezinárodní soutež ve fyzice"));
	}

	public function renderLastYears() {
		$this->setPagetitle(_("Minulé ročníky"));
	}

	public function renderLogin() {
		$this->setPagetitle(_("Přihlásit se"));
	}

	public function renderRules() {
		$this->setPagetitle(_("Pravidla"));
	}

	public function renderTaskExamples() {
		$this->setPagetitle(_("Rozcvička"));
	}

	// ----- PROTECTED METHODS

	protected function createComponentChat($name) {
		$chat = new ChatListComponent($this, $name);
		return $chat;
	}
	
	protected function createComponentLogin($name) {
		return new LoginFormComponent($this, $name);
	}

}
