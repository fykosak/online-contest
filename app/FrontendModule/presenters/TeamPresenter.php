<?php

class Frontend_TeamPresenter extends Frontend_BasePresenter {

    public function actionRegistration() {
        if (!Interlos::isRegistrationActive()) {
            $this->flashMessage("Registrace není aktivní.", "error");
            $this->redirect("Default:default");
        }
    }

    public function renderDefault() {
        $this->setPageTitle(Interlos::getLoggedTeam()->name);
    }

    public function renderList() {
        $this->setPageTitle("Seznam týmů");
        $this->getComponent("teamList")->setSource(
                Interlos::teams()->findAll()
        );
        $this->getTemplate()->categories = Interlos::teams()->getCategoryNames();
    }

    public function renderRegistration() {
        $this->setPageTitle("Registrace");
    }

    // ---- PROTECTED METHODS

    protected function createComponentTeamForm($name) {
        return new TeamFormComponent($this, $name);
    }

    protected function createComponentTeamList($name) {
        return new TeamListComponent($this, $name);
    }

}
