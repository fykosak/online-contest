<?php

namespace FOL\Modules\GameModule;

use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTaskState;
use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ORM\Services\ServiceTaskState;
use FOL\Model\ORM\TasksService;
use FOL\Model\ScoreStrategy;
use Nette\Database\Table\ActiveRow;
use Tracy\Debugger;

class TaskPresenter extends BasePresenter {

    private TasksService $tasksService;
    private ServiceTaskState $serviceTaskState;
    private ServiceTask $serviceTask;
    public ScoreStrategy $scoreStrategy;

    public function injectSecondary(TasksService $tasksService, ServiceTaskState $serviceTaskState, ServiceTask $serviceTask, ScoreStrategy $scoreStrategy): void {
        $this->tasksService = $tasksService;
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
        foreach ($this->tasksService->findProblemAvailable($team)->fetchAssoc('id_task') as $taskId => $datum) {
            if (isset($solved[$taskId])) {
                $solvedTasks[] = $taskId;
            } elseif (isset($skipped[$taskId])) {
                $skippedTasks[] = $taskId;
            } elseif (isset($unsolved[$taskId])) {
                $unsolvedTasks[] = $taskId;
            } else {
                $missedTasks[] = $taskId;
            }
        }
        $this->template->serviceTask = $this->serviceTask;
        $this->template->solvedTasks = $this->serviceTask->getTable()->where('id_task', $solvedTasks);
        $this->template->skippedTasks = $this->serviceTask->getTable()->where('id_task', $skippedTasks);
        $this->template->unsolvedTasks = $this->serviceTask->getTable()->where('id_task', $unsolvedTasks);
        $this->template->missedTasks = $this->serviceTask->getTable()->where('id_task', $missedTasks);
    }
}
