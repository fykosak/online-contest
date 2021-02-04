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
    private ServiceTask $serviceTask;
    public ScoreStrategy $scoreStrategy;

    public function injectSecondary(TasksService $tasksService, ServiceTask $serviceTask, ScoreStrategy $scoreStrategy): void {
        $this->tasksService = $tasksService;
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
        $solved = $team->getSolved()->fetchPairs('id_task', 'id_task');
        $skipped = $team->getSkipped()->fetchPairs('id_task', 'id_task');
        $unsolved = $team->getSubmitAvailableTasks()->select('group:task.id_task AS id_task')->fetchPairs('id_task', 'id_task');

        $unsolvedTasks = [];
        $skippedTasks = [];
        $solvedTasks = [];
        $missedTasks = [];

        $query = $team->getAvailableTasks()
            ->order('group.id_group')
            ->order('group:task.number')
            ->select('group:task.id_task AS id_task')
            ->fetchAssoc('id_task');

        /** @var ActiveRow|ModelTaskState $row */
        foreach ($query as $taskId => $datum) {
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
        $this->template->solvedTasks = $this->serviceTask->getTable()->where('id_task', $solvedTasks);
        $this->template->skippedTasks = $this->serviceTask->getTable()->where('id_task', $skippedTasks);
        $this->template->unsolvedTasks = $this->serviceTask->getTable()->where('id_task', $unsolvedTasks);
        $this->template->missedTasks = $this->serviceTask->getTable()->where('id_task', $missedTasks);
    }
}
