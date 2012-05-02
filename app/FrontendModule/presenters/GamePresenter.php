<?php

class Frontend_GamePresenter extends Frontend_BasePresenter {

    public function renderAnswer() {
        $this->setPageTitle("Odevzdat řešení");
    }

    public function renderSkip() {
        $this->setPageTitle("Přeskočit úkol");
    }

    public function renderDefault() {
        $this->setPageTitle("Zadání");
        $team = Interlos::getLoggedTeam()->id_team;
        Interlos::tasks()->updateCounter($team);
        $this->getTemplate()->tasks = Interlos::tasks()->findProblemAvailable($team);
        $this->getTemplate()->mirrors = (array) Environment::getConfig("tasks")->mirrors;
    }

    public function renderHistory() {
        $this->setPageTitle("Historie odpovědí");
        $this->getComponent("answerHistory")->setSource(
                Interlos::answers()->findAll()
                        ->where("[id_team] = %i", Interlos::getLoggedTeam()->id_team)
                        ->orderBy("inserted", "DESC")
        );
        $this->getComponent("answerHistory")->setLimit(50);
    }

    protected function startUp() {
        parent::startUp();
        if (Interlos::getLoggedTeam() == null) {
            $this->flashMessage("Do této sekce mají přístup pouze přihlášené týmy.", "error");
            $this->redirect("Default:default");
        }
    }

    protected function createComponentAnswerForm($name) {
        return new AnswerFormComponent($this, $name);
    }

    protected function createComponentAnswerHistory($name) {
        return new AnswerHistoryComponent($this, $name);
    }

    protected function createComponentSkipForm($name) {
        return new SkipFormComponent($this, $name);
    }

}