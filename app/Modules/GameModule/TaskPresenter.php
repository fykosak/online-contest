<?php

namespace FOL\Modules\GameModule;

use Dibi\Exception;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;

class TaskPresenter extends BasePresenter {

    private TasksService $tasksService;
    private ScoreService $scoreService;

    public function injectSecondary(TasksService $tasksService, ScoreService $scoreService): void {
        $this->tasksService = $tasksService;
        $this->scoreService = $scoreService;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function renderDefault(): void {
        $this->setPageTitle(_('Zadání'));
        $team = $this->getLoggedTeam()->id_team;
        $this->getTemplate()->id_team = $team;

        $mirrors = (array)$this->context->parameters['tasks']['mirrors'];
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
}
