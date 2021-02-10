<?php

namespace FOL\Model\ORM;

use DateTime;
use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelGroup;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTaskState;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceLog;
use FOL\Model\ORM\Services\ServiceTaskState;
use Fykosak\Utils\ORM\Exceptions\ModelException;
use Nette\Database\Explorer;
use Nette\InvalidStateException;
use Tracy\Debugger;

final class TasksService extends AbstractService {

    private ServiceTaskState $serviceTaskState;

    public function __construct(ServiceLog $serviceLog, Explorer $explorer, ServiceTaskState $serviceTaskState) {
        parent::__construct($explorer, $serviceLog);
        $this->serviceTaskState = $serviceTaskState;
    }

    public function skip(ModelTeam $team, ModelTask $task): ModelTaskState {
        // Check that skip is allowed for task
        try {
            $return = $this->serviceTaskState->createNewModel([
                'id_team' => $team->id_team,
                'id_task' => $task->id_task,
                'inserted' => new DateTime(),
                'skipped' => 1,
                'points' => null,
            ]);
        } catch (ModelException$exception) {
            $this->log($team->id_team, 'skip_tried', sprintf('The team tried to skip the task [%i].', $task->id_task));
            throw new InvalidStateException(sprintf(sprintf('Skipping not allowed for the task %i.', $task->id_task)), AnswersService::ERROR_SKIP_OF_ANSWERED);
        }

        // Increase counter
        $this->explorer->query('INSERT INTO group_state (id_group, id_team, task_counter)
                    VALUES(?, ?, 0)
                ON DUPLICATE KEY UPDATE task_counter = task_counter + 1', $task->id_group, $team->id_team);

        // Log the action
        $this->log($team->id_team, 'task_skipped', 'The team successfuly skipped the task [$task->id_task].');
        return $return;
    }

    public function updateCounter(bool $full = false): void {
        // Initialize with zeroes
        if ($full) {
            $this->explorer->query('INSERT INTO group_state (id_group, id_team, task_counter)
                    SELECT id_group, id_team, 0
                    FROM `group`, team
                ON DUPLICATE KEY UPDATE task_counter = task_counter');
        }
        // Update according to current period
        $this->explorer->query('UPDATE group_state AS gs
                SET task_counter = 
                    GREATEST(
                        IFNULL(
                            (
                                SELECT COUNT(id_task)
                                FROM task_state AS ts
                                    LEFT JOIN task tsk2 USING (id_task)
                                WHERE ts.id_team = gs.id_team AND tsk2.id_group = gs.id_group AND (skipped = 1 OR (tsk2.`cancelled` = 0 AND ts.points IS NOT NULL))
                            )+ (
                                SELECT reserve_size
                                FROM period AS p 
                                WHERE p.id_group = gs.id_group AND p.begin <= NOW() AND p.end > NOW()
                            ) + (
                                SELECT COUNT(id_task)
                                FROM task
                                WHERE number <= gs.task_counter AND cancelled = 1
                            )+(
                                SELECT COUNT(*) 
                                FROM `card_usage` cu
                                WHERE `cu`.card_type=? AND team_id=gs.id_team AND cu.data=gs.id_group                                
                            ), 0),
                    gs.task_counter)', ModelCardUsage::TYPE_ADD_TASK);
    }

    public function updateSingleCounter(ModelTeam $team, ModelGroup $group): void {
        $this->explorer->query('UPDATE group_state AS gs
                SET task_counter = 
                    GREATEST(
                        IFNULL(
                            (
                                SELECT COUNT(id_task)
                                FROM task_state AS ts
                                    LEFT JOIN task tsk2 USING (id_task)
                                WHERE ts.id_team = gs.id_team AND tsk2.id_group = gs.id_group AND (skipped = 1 OR (tsk2.`cancelled` = 0 AND ts.points IS NOT NULL))
                            ) + (
                                SELECT reserve_size
                                FROM period AS p
                                WHERE p.id_group = gs.id_group AND p.begin <= NOW() AND p.end > NOW()
                             ) + (
                                SELECT COUNT(id_task)
                                FROM task
                                WHERE number <= gs.task_counter AND cancelled = 1
                             )+(
                                SELECT COUNT(*) 
                                FROM `card_usage` cu
                                WHERE `cu`.card_type=? AND team_id=gs.id_team AND cu.data=gs.id_group                                
                            ), 0),
                    gs.task_counter)
                WHERE gs.id_group = ? AND gs.id_team = ?', ModelCardUsage::TYPE_ADD_TASK, $group->id_group, $team->id_team);
    }

    public function updateSingleCounter2(ModelTeam $team, ModelGroup $group): void {
        $period = $group->getActivePeriod();
        $usage = $team->getCardUsageByType(ModelCardUsage::TYPE_ADD_TASK);
        $this->explorer->query('UPDATE group_state
                SET task_counter = GREATEST(?,group_state.task_counter)
                WHERE group_state.id_group = ? AND group_state.id_team = ?',
            $team->getSolvedOrSkippedOrCanceled()->count('id_task') + ($period ? $period->reserve_size : 0) + (($usage && $usage->getData() == $group->id_group) ? 1 : 0),
            $group->id_group,
            $team->id_team
        );
    }

    public function increaseCounter(ModelTeam $team, ModelGroup $group): void {
        $this->explorer->query('UPDATE group_state SET task_counter = task_counter + 1 WHERE group_state.id_group = ? AND group_state.id_team = ?',
            $group->id_group,
            $team->id_team
        );
    }
}
