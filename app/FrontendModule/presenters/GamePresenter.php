<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos;

class GamePresenter extends BasePresenter {

    public function renderAnswer() {
        $this->setPageTitle(_("Odevzdat řešení"));
    }

    public function renderSkip() {
        $this->setPageTitle(_("Přeskočit úkol"));
    }

    public function renderDefault() {
        $this->setPageTitle(_("Zadání"));
        $team = Interlos::getLoggedTeam($this->user)->id_team;
        $this->getTemplate()->id_team = $team;
        
        $mirrors = (array) $this->context->parameters["tasks"]["mirrors"];
        shuffle($mirrors);
        $this->getTemplate()->mirrors = $mirrors;
        
        // tasks
        $solved = Interlos::tasks()->findSolved($team);
        $skipped = Interlos::tasks()->findSkipped($team);
        
        $unsolvedTasks = array();
        $skippedTasks = array();
        $solvedTasks = array();
        foreach(Interlos::tasks()->findProblemAvailable($team) as $task){
            if(isset($solved[$task->id_task])){
                $solvedTasks[] = $task;
            }elseif(isset($skipped[$task->id_task])){
                $skippedTasks[] = $task;
            }else{
                $unsolvedTasks[] = $task;
            }
        }
        $this->template->solvedTasks = $solvedTasks;
        $this->template->skippedTasks = $skippedTasks;
        $this->template->unsolvedTasks = $unsolvedTasks;
        
    }

    public function renderHistory() {
        $this->setPageTitle(_("Historie odpovědí"));
        $this->getComponent("answerHistory")->setSource(
                Interlos::answers()->findAll()
                        ->where("[id_team] = %i", Interlos::getLoggedTeam($this->user)->id_team)
                        ->orderBy("inserted", "DESC")
        );
        $this->getComponent("answerHistory")->setLimit(50);
    }

    protected function startUp() {
        parent::startUp();
        if (Interlos::getLoggedTeam($this->user) == null) {
            $this->flashMessage(_("Do této sekce mají přístup pouze přihlášené týmy."), "danger");
            $this->redirect("Default:default");
        }
    }

    protected function createComponentAnswerForm($name) {
        return new \AnswerFormComponent($this, $name);
    }

    protected function createComponentAnswerHistory($name) {
        return new \AnswerHistoryComponent($this, $name);
    }

    protected function createComponentSkipForm($name) {
        return new \SkipFormComponent($this, $name);
    }

}