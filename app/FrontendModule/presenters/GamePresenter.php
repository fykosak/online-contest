<?php

namespace App\FrontendModule\Presenters;

use AnswerFormComponent;
use AnswerHistoryComponent;
use App\Model\Interlos;
use SkipFormComponent;

class GamePresenter extends BasePresenter {

    public function renderAnswer(): void {
        $this->setPageTitle(_("Odevzdat řešení"));
    }

    public function renderSkip(): void {
        if (!$this->user->isAllowed('task', 'skip')) {
            $this->flashMessage(_('Již jste vyčerpali svůj limit pro počet přeskočených úloh.'), "danger");
            $this->redirect("default");
        }

        $this->setPageTitle(_("Přeskočit úkol"));
    }

    public function renderDefault(): void {
        $this->setPageTitle(_("Zadání"));
        $team = Interlos::getLoggedTeam($this->user)->id_team;
        $this->getTemplate()->id_team = $team;

        $mirrors = (array)$this->context->parameters["tasks"]["mirrors"];
        shuffle($mirrors);
        $this->getTemplate()->mirrors = $mirrors;

        // tasks
        $solved = Interlos::tasks()->findSolved($team);
        $skipped = Interlos::tasks()->findSkipped($team);
        $unsolved = Interlos::tasks()->findUnsolved($team);

        $unsolvedTasks = [];
        $skippedTasks = [];
        $solvedTasks = [];
        $missedTasks = [];
        foreach (Interlos::tasks()->findProblemAvailable($team) as $task) {
            $task->curPoints = Interlos::score()->getSingleTaskScore($team, $task);
            if (isset($solved[$task->id_task])) {
                $solvedTasks[] = $task;
            } elseif (isset($skipped[$task->id_task])) {
                $skippedTasks[] = $task;
            } elseif (isset($unsolved[$task->id_task])) {
                $unsolvedTasks[] = $task;
            } else {
                $missedTasks[] = $task;
            }
        }
        $this->template->solvedTasks = $solvedTasks;
        $this->template->skippedTasks = $skippedTasks;
        $this->template->unsolvedTasks = $unsolvedTasks;
        $this->template->missedTasks = $missedTasks;

    }

    public function actionHistory(): void {
        //has to be loaded in action due to pagination
        $this->getComponent("answerHistory")->setSource(
            Interlos::answers()->findAll()
                ->where("[id_team] = %i", Interlos::getLoggedTeam($this->user)->id_team)
                ->orderBy("inserted", "DESC")
        );
        $this->getComponent("answerHistory")->setLimit(50);
    }

    public function renderHistory(): void {
        $this->setPageTitle(_("Historie odpovědí"));
    }

    protected function startUp(): void {
        parent::startUp();
        if (Interlos::getLoggedTeam($this->user) == null) {
            $this->flashMessage(_("Do této sekce mají přístup pouze přihlášené týmy."), "danger");
            $this->redirect("Default:default");
        }
    }

    protected function createComponentAnswerForm(): AnswerFormComponent {
        return new AnswerFormComponent();
    }

    protected function createComponentAnswerHistory(): AnswerHistoryComponent {
        return new AnswerHistoryComponent();
    }

    protected function createComponentSkipForm(): SkipFormComponent {
        return new SkipFormComponent();
    }

}
