<?php
class Frontend_DefaultPresenter extends Frontend_BasePresenter {

	public function actionLogout() {
		Environment::getUser()->logout();
		$this->redirect("default");
	}

	public function renderChat() {
		$this->getComponent("chat")->setSource(Interlos::chat()->findAll());
		$this->setPageTitle("Diskuse");
	}

	public function renderDefault() {
		$this->setPagetitle("Mezinárodní soutež ve fyzice");
	}

	public function renderLastYears() {
		$this->setPagetitle("Minulé ročníky");
	}

	public function renderLogin() {
		$this->setPagetitle("Přihlásit se");
	}

	public function renderRules() {
		$this->setPagetitle("Pravidla");
	}

	public function renderTaskExamples() {
		$this->setPagetitle("Rozcvička");
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
