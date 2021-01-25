<?php

namespace FOL\Modules\GameModule;

use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTaskState;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ORM\Services\ServiceTaskState;
use FOL\Model\ORM\TasksService;
use FOL\Model\ScoreStrategy;
use Nette\Database\Table\ActiveRow;

class TaskPresenter extends BasePresenter {

    private TasksService $tasksService;
    public ScoreService $scoreService;
    private ServiceTaskState $serviceTaskState;
    private ServiceTask $serviceTask;
    public ScoreStrategy $scoreStrategy;

    public function injectSecondary(TasksService $tasksService, ScoreService $scoreService, ServiceTaskState $serviceTaskState, ServiceTask $serviceTask, ScoreStrategy $scoreStrategy): void {
        $this->tasksService = $tasksService;
        $this->scoreService = $scoreService;
        $this->serviceTaskState = $serviceTaskState;
        $this->serviceTask = $serviceTask;
        $this->scoreStrategy = $scoreStrategy;
    }

    public function renderDefault(): void {
        $this->setPageTitle(_('Zadání'));
        $team = $this->getLoggedTeam();
        $this->template->team = $team;

        $mirrors = (array)$this->context->parameters['tasks']['mirrors'];
        shuffle($mirrors);
        $this->template->mirrors = $mirrors;

        // tasks
        $solved = $this->serviceTaskState->findSolved($team)->fetchPairs('id_task', 'id_task');
        $skipped = $this->serviceTaskState->findSkipped($team)->fetchPairs('id_task', 'id_task');
        $unsolved = $this->tasksService->findUnsolved($team);

        $unsolvedTasks = [];
        $skippedTasks = [];
        $solvedTasks = [];
        $missedTasks = [];
        /** @var ActiveRow|ModelTaskState $row */
        foreach ($this->tasksService->findProblemAvailable($team) as $row) {
            /** @var ModelTask $task */
            $task = $this->serviceTask->findByPrimary($row->id_task);
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
