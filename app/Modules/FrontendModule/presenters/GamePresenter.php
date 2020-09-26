<?php

namespace App\FrontendModule\Presenters;

use AnswerFormComponent;
use AnswerHistoryComponent;
use Dibi\Exception;
use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use Nette\Application\AbortException;
use SkipFormComponent;

class GamePresenter extends BasePresenter {

    private AnswersService $answersService;

    private TasksService $tasksService;

    private ScoreService $scoreService;

    public function injectSecondary(AnswersService $answersService, TasksService $tasksService, ScoreService $scoreService): void {
        $this->answersService = $answersService;
        $this->tasksService = $tasksService;
        $this->scoreService = $scoreService;
    }

    public function renderAnswer(): void {
        $this->setPageTitle(_("Odevzdat řešení"));
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function renderSkip(): void {
        if (!$this->user->isAllowed('task', 'skip')) {
            $this->flashMessage(_('Již jste vyčerpali svůj limit pro počet přeskočených úloh.'), "danger");
            $this->redirect("default");
        }

        $this->setPageTitle(_("Přeskočit úkol"));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function renderDefault(): void {
        $this->setPageTitle(_("Zadání"));
        $team = $this->getLoggedTeam()->id_team;
        $this->getTemplate()->id_team = $team;

        $mirrors = (array)$this->context->parameters["tasks"]["mirrors"];
        shuffle($mirrors);
        $this->getTemplate()->mirrors = $mirrors;

        // tasks
        $solved = $this->tasksService->findSolved($team);
        $skipped = $this->tasksService->findSkipped($team);
        $unsolved = $this->tasksService->findUnsolved($team);

        $unsolvedTasks = [];
        $skippedTasks = [];
        $solvedTasks = [];
        $missedTasks = [];
        foreach ($this->tasksService->findProblemAvailable($team) as $task) {
            $task->curPoints = $this->scoreService->getSingleTaskScore($team, $task);
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

    /**
     * @return void
     * @throws Exception
     */
    public function actionHistory(): void {
        //has to be loaded in action due to pagination
        $this->getComponent("answerHistory")->setSource(
            $this->answersService->findAll()
                ->where("[id_team] = %i", $this->getLoggedTeam()->id_team)
                ->orderBy("inserted", "DESC")
        );
        $this->getComponent("answerHistory")->setLimit(50);
    }

    public function renderHistory(): void {
        $this->setPageTitle(_("Historie odpovědí"));
    }

    /**
     * @return void
     * @throws Exception
     * @throws AbortException
     */
    protected function startUp(): void {
        parent::startUp();
        if ($this->getLoggedTeam() == null) {
            $this->flashMessage(_("Do této sekce mají přístup pouze přihlášené týmy."), "danger");
            $this->redirect("Default:default");
        }
    }

    protected function createComponentAnswerForm(): AnswerFormComponent {
        return new AnswerFormComponent($this->getContext());
    }

    protected function createComponentAnswerHistory(): AnswerHistoryComponent {
        return new AnswerHistoryComponent($this->getContext());
    }

    protected function createComponentSkipForm(): SkipFormComponent {
        return new SkipFormComponent($this->getContext());
    }

}
