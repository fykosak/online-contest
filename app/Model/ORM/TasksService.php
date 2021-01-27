<?php

namespace FOL\Model\ORM;

use DateTime;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceGroup;
use FOL\Model\ORM\Services\ServiceLog;
use FOL\Model\ORM\Services\ServiceTaskState;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Traversable;

class TasksService extends AbstractService {

    const TYPE_STR = 'str';
    const TYPE_INT = 'int';
    const TYPE_REAL = 'real';

    protected AnswersService $answersService;

    protected ServiceGroup $serviceGroup;
    private ServiceTaskState $serviceTaskState;

    public function __construct(AnswersService $answersService, ServiceGroup $serviceGroup, ServiceLog $serviceLog, Explorer $explorer, ServiceTaskState $serviceTaskState) {
        parent::__construct($explorer, $serviceLog);
        $this->answersService = $answersService;
        $this->serviceGroup = $serviceGroup;
        $this->serviceTaskState = $serviceTaskState;
    }

    public function findPossiblyAvailable(): Selection {
        return $this->explorer->table('view_possibly_available_task')
            ->order('id_group')
            ->order('number');
    }

    public function findProblemAvailable(ModelTeam $team): Selection {
        return $this->explorer->table('view_available_task')
            ->where('id_team', $team->id_team)
            ->order('id_group')
            ->order('number');
    }

    public function findSubmitAvailable(ModelTeam $team): Selection {
        $source = $this->explorer->table('view_submit_available_task')
            ->where('id_team', $team->id_team);

        $solved = $this->serviceTaskState->findSolved($team)->fetchPairs('id_task', 'id_task');

        // Remove solved tasks from the source
        if (count($solved)) {
            $source->where('id_task NOT IN ?', $solved);
        }
        return $source;
    }

    /**
     * Find missed tasks (after end of hurry up)
     *
     * @param ModelTeam $team
     * @return array id_task => id_task
     */
    public function findMissed(ModelTeam $team): array {
        $source = $this->explorer->query('SELECT `view_available_task`.* FROM view_available_task
            RIGHT JOIN `period` ON `period`.`id_group` = `view_available_task`.`id_group`
            AND (`period`.`begin` > NOW() OR `period`.`end` < NOW()) WHERE id_team = ?', $team->id_team);
        return $source->fetchPairs('id_task', 'id_task');
    }

    /**
     * Find unsolved tasks, which can be submitted (i.e. not hurry up after its end)
     *
     * @param ModelTeam $team
     * @return array id_task => id_task
     */
    public function findUnsolved(ModelTeam $team): array {
        return $this->findSubmitAvailable($team)->fetchPairs('id_task', 'id_task');
    }

    public function findAllStats(): Selection {
        return $this->explorer->table('tmp_task_stat')->order('id_group')->order('number');
    }

    /**
     * @param ModelTeam $team
     * @param ModelTask $task
     * @return array|bool|int|iterable|ActiveRow|Selection|Traversable
     */
    public function skip(ModelTeam $team, ModelTask $task) {
        // Check that skip is allowed for task
        $answers = $this->answersService->findAllCorrect($team->id_team)->where('id_task = ?', $task->id_task);
        if ($answers->count() > 0) {
            $this->log($team->id_team, 'skip_tried', sprintf('The team tried to skip the task [%i].', $task->id_task));
            throw new InvalidStateException(sprintf('Skipping not allowed for the task %i.', $task->id_task), AnswersService::ERROR_SKIP_OF_ANSWERED);
        }

        // Check that skip is allowed in period
        $skippAbleGroups = $this->serviceGroup->findAllSkippAble()->fetchPairs('id_group', 'id_group');
        if (!array_key_exists($task['id_group'], $skippAbleGroups)) {
            $this->log($team->id_team, 'skip_tried', 'The team tried to skip the task [$task->id_task].');
            throw new InvalidStateException('Skipping not allowed during this period.', AnswersService::ERROR_SKIP_OF_PERIOD);
        }
        // Insert a skip record
        $return = $this->explorer->table('task_state')->insert([
            'id_team' => $team->id_team,
            'id_task' => $task['id_task'],
            'inserted' => new DateTime(),
            'skipped' => 1,
            'points' => null,
        ]);

        // Increase counter
        $sql = 'INSERT INTO group_state (id_group, id_team, task_counter)
                    VALUES(?, ?, 0)
                ON DUPLICATE KEY UPDATE task_counter = task_counter + 1';
        $this->explorer->query($sql, $task->id_group, $team->id_team);

        // Log the action
        $this->log($team->id_team, 'task_skipped', 'The team successfuly skipped the task [$task->id_task].');
        return $return;
    }

    public function updateCounter(bool $full = false): void {
        // Initialize with zeroes
        $sql = 'INSERT INTO group_state (id_group, id_team, task_counter)
                    SELECT id_group, id_team, 0
                    FROM `group`, team
                ON DUPLICATE KEY UPDATE task_counter = task_counter';
        if ($full) {
            $this->explorer->query($sql);
        }

        // Update according to current period
        $sql = 'UPDATE group_state AS gs
                SET task_counter = 
                    GREATEST(
                        IFNULL(
                            (
                                SELECT COUNT(id_answer)
                                FROM view_correct_answer AS ca
                                LEFT JOIN task tsk USING (id_task)
                                WHERE ca.id_team = gs.id_team AND tsk.id_group = gs.id_group
                            ) + (
                                SELECT COUNT(id_task)
                                FROM task_state AS ts
                                LEFT JOIN task tsk2 USING (id_task)
                                WHERE ts.id_team = gs.id_team AND tsk2.id_group = gs.id_group AND skipped = 1
                            ) + (
                                SELECT reserve_size
                                FROM period AS p 
                                WHERE p.id_group = gs.id_group AND p.begin <= NOW() AND p.end > NOW()
                            ) + (
                                SELECT COUNT(id_task)
                                FROM task
                                WHERE number <= gs.task_counter AND cancelled = 1
                            ), 0),
                    gs.task_counter)';

        $this->explorer->query($sql);
    }

    public function updateSingleCounter(ModelTeam $team, ModelTask $task): void {
        $sql = 'UPDATE group_state AS gs
                SET task_counter = 
                    GREATEST(
                        IFNULL(
                            (
                                SELECT COUNT(id_answer)
                                FROM view_correct_answer AS ca
                                LEFT JOIN task tsk USING (id_task)
                                WHERE ca.id_team = gs.id_team AND tsk.id_group = gs.id_group
                             ) + (
                                SELECT COUNT(id_task)
                                FROM task_state AS ts
                                LEFT JOIN task tsk2 USING (id_task)
                                WHERE ts.id_team = gs.id_team AND tsk2.id_group = gs.id_group AND skipped = 1
                             ) + (
                                SELECT reserve_size
                                FROM period AS p
                                WHERE p.id_group = gs.id_group AND p.begin <= NOW() AND p.end > NOW()
                             ) + (
                                SELECT COUNT(id_task)
                                FROM task
                                WHERE number <= gs.task_counter AND cancelled = 1
                             ), 0),
                    gs.task_counter)
                WHERE gs.id_group = ? AND gs.id_team = ?';

        $this->explorer->query($sql, $task->id_group, $team->id_team);
    }

    public static function checkAnswer(ModelTask $task, string $solution): bool {
        switch ($task->answer_type) {
            case self::TYPE_STR:
                return $solution == $task->answer_str;
            case self::TYPE_INT:
                return $solution == $task->answer_int;
            case self::TYPE_REAL:
                return ($task->answer_real - $task->real_tolerance <= $solution) && ($solution <= $task->answer_real + $task->real_tolerance);
        }
        throw new InvalidArgumentException();
    }
}
