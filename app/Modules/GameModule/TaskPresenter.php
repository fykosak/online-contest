<?php

namespace FOL\Modules\GameModule;

use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ScoreStrategy;
use Tracy\Debugger;

class TaskPresenter extends BasePresenter {

    private ServiceTask $serviceTask;
    private ScoreStrategy $scoreStrategy;

    public function injectSecondary(ServiceTask $serviceTask, ScoreStrategy $scoreStrategy): void {
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
        $unsolved = $team->getSubmitAvailableTasks()
            ->select('group:task.id_task')
            ->fetchPairs('id_task', 'id_task');

        $unsolvedTasks = [];
        $skippedTasks = [];
        $solvedTasks = [];
        $missedTasks = [];

        $query = $team->getAvailableTasks()
            ->order('group.id_group')
            ->order('group:task.number')->select('group:task.id_task AS id_task');

        foreach ($query as $datum) {
            if (isset($solved[$datum->id_task])) {
                $solvedTasks[] = $datum->id_task;
            } elseif (isset($skipped[$datum->id_task])) {
                $skippedTasks[] = $datum->id_task;
            } elseif (isset($unsolved[$datum->id_task])) {
                $unsolvedTasks[] = $datum->id_task;
            } else {
                $missedTasks[] = $datum->id_task;
            }
        }
        $this->template->scoreStrategy = $this->scoreStrategy;
        $this->template->solvedTasks = $this->serviceTask->getTable()->where('id_task', $solvedTasks);
        $this->template->skippedTasks = $this->serviceTask->getTable()->where('id_task', $skippedTasks);
        $this->template->unsolvedTasks = $this->serviceTask->getTable()->where('id_task', $unsolvedTasks);
        $this->template->missedTasks = $this->serviceTask->getTable()->where('id_task', $missedTasks);
    }
}
