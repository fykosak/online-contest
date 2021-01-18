<?php

namespace FOL\Model\ORM;

use DateTime;
use Dibi\Connection as DibiConnection;
use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Result;
use Dibi\Row;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use Nette\Database\Explorer;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;

class TasksService extends AbstractService {

    const TYPE_STR = 'str';
    const TYPE_INT = 'int';
    const TYPE_REAL = 'real';

    protected AnswersService $answersService;

    protected GroupsService $groupsService;

    public function __construct(Explorer $explorer, DibiConnection $dibiConnection, AnswersService $answersService, GroupsService $groupsService) {
        parent::__construct($explorer, $dibiConnection);
        $this->answersService = $answersService;
        $this->groupsService = $groupsService;
    }

    /**
     * @param $id
     * @return Row|null
     * @throws Exception
     */
    public function find(int $id): ?Row {
        return $this->findAll()->where('[id_task] = %i', $id)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAll(): DataSource {
        return $this->getDibiConnection()->dataSource('SELECT * FROM [view_task]')
            ->orderBy('id_group')
            ->orderBy('number');
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findPossiblyAvailable(): DataSource {
        return $this->getDibiConnection()->dataSource('SELECT * FROM [view_possibly_available_task]')
            ->orderBy('id_group')
            ->orderBy('number');
    }

    /**
     * @param ModelTeam $team
     * @return DataSource
     * @throws Exception
     */
    public function findProblemAvailable(ModelTeam $team): DataSource {
        return $this->getDibiConnection()->dataSource('SELECT * FROM [view_available_task] WHERE [id_team] = %i', $team->id_team)
            ->orderBy('id_group')
            ->orderBy('number');
    }

    /**
     * @param ModelTeam $team
     * @return DataSource
     * @throws Exception
     */
    public function findSubmitAvailable(ModelTeam $team): DataSource {
        $source = $this->getDibiConnection()->dataSource('SELECT * FROM [view_submit_available_task] WHERE [id_team] = %i', $team->id_team)
            ->orderBy('id_group')
            ->orderBy('number');

        $solved = $this->findSolved($team);

        // Remove solved tasks from the source
        if (!empty($solved)) {
            $source->where('[id_task] NOT IN %l', $solved);
        }
        return $source;
    }

    /**
     * Find missed tasks (after end of hurry up)
     *
     * @param ModelTeam $team
     * @return array id_task => id_task
     * @throws Exception
     */
    public function findMissed(ModelTeam $team): array {
        $source = $this->getDibiConnection()->dataSource('SELECT `view_available_task`.* FROM [view_available_task]
            RIGHT JOIN `period` ON `period`.`id_group` = `view_available_task`.`id_group`
            AND (`period`.`begin` > NOW() OR `period`.`end` < NOW()) WHERE [id_team] = %i', $team->id_team);
        return $source->fetchPairs('id_task', 'id_task');
    }

    /**
     * Find unsolved tasks, which can be submitted (i.e. not hurry up after its end)
     *
     * @param ModelTeam $team
     * @return array id_task => id_task
     * @throws Exception
     */
    public function findUnsolved(ModelTeam $team): array {
        return $this->findSubmitAvailable($team)->fetchPairs('id_task', 'id_task');
    }

    /**
     * Find solved tasks
     *
     * @param ModelTeam $team
     * @return array id_task => id_task
     * @throws Exception
     */
    public function findSolved(ModelTeam $team): array {
        $source = $this->getDibiConnection()->dataSource('SELECT id_task FROM [task_state] WHERE [id_team] = %i', $team->id_team, ' and points IS NOT NULL');
        return $source->fetchPairs('id_task', 'id_task');
    }

    /**
     * Find skipped tasks
     *
     * @param ModelTeam $team
     * @return array id_task => id_task
     * @throws Exception
     */
    public function findSkipped(ModelTeam $team): array {
        $source = $this->getDibiConnection()->dataSource('SELECT id_task FROM [task_state] WHERE [id_team] = %i', $team->id_team, ' and skipped = 1');
        return $source->fetchPairs('id_task', 'id_task');
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAllStats(): DataSource {
        return $this->getDibiConnection()->dataSource('SELECT * FROM [tmp_task_stat]')
            ->orderBy('id_group')
            ->orderBy('number');
    }

    /**
     * @param $team
     * @param mixed|ModelTask $task
     * @return Result|int
     * @throws Exception
     */
    public function skip(ModelTeam $team, ModelTask $task) {
        // Check that skip is allowed for task
        $answers = $this->answersService->findAllCorrect($team->id_team)->where('[id_task] = %i', $task->id_task);
        if ($answers->count() > 0) {
            $this->log($team->id_team, 'skip_tried', 'The team tried to skip the task [$task->id_task].');
            throw new InvalidStateException('Skipping not allowed for the task [$task->id_task].', AnswersService::ERROR_SKIP_OF_ANSWERED);
        }

        // Check that skip is allowed in period
        $skippAbleGroups = $this->groupsService->findAllSkippable()->fetchPairs('id_group', 'id_group');
        if (!array_key_exists($task['id_group'], $skippAbleGroups)) {
            $this->log($team->id_team, 'skip_tried', 'The team tried to skip the task [$task->id_task].');
            throw new InvalidStateException('Skipping not allowed during this period.', AnswersService::ERROR_SKIP_OF_PERIOD);
        }
        // Insert a skip record
        $return = $this->getDibiConnection()->insert('task_state', [
            'id_team' => $team->id_team,
            'id_task' => $task['id_task'],
            'inserted' => new DateTime(),
            'skipped' => 1,
            'points' => null,
        ])->execute();

        // Increase counter
        $sql = 'INSERT INTO [group_state] ([id_group], [id_team], [task_counter])
                    VALUES(%i, %i, 0)
                ON DUPLICATE KEY UPDATE [task_counter] = [task_counter] + 1';
        $this->getDibiConnection()->query($sql, $task->id_group, $team->id_team);

        // Log the action
        $this->log($team->id_team, 'task_skipped', 'The team successfuly skipped the task [$task->id_task].');
        return $return;
    }

    /**
     * @param false $full
     * @return void
     * @throws Exception
     */
    public function updateCounter(bool $full = false) {
        // Initialize with zeroes
        $sql = 'INSERT INTO [group_state] ([id_group], [id_team], [task_counter])
                    SELECT [id_group], [id_team], 0
                    FROM [view_group], [view_team]
                ON DUPLICATE KEY UPDATE [task_counter] = [task_counter]';
        if ($full) {
            $this->getDibiConnection()->query($sql);
        }

        // Update according to current period
        $sql = 'UPDATE group_state AS gs
                SET task_counter = 
                    GREATEST(
                        IFNULL(
                            (
                                SELECT COUNT(id_answer)
                                FROM view_correct_answer AS ca
                                LEFT JOIN view_task tsk USING (id_task)
                                WHERE ca.id_team = gs.id_team AND tsk.id_group = gs.id_group
                            ) + (
                                SELECT COUNT(id_task)
                                FROM task_state AS ts
                                LEFT JOIN view_task tsk2 USING (id_task)
                                WHERE ts.id_team = gs.id_team AND tsk2.id_group = gs.id_group AND skipped = 1
                            ) + (
                                SELECT reserve_size
                                FROM period AS p 
                                WHERE p.id_group = gs.id_group AND p.begin <= NOW() AND p.end > NOW()
                            ) + (
                                SELECT COUNT(id_task)
                                FROM view_task
                                WHERE number <= gs.task_counter AND cancelled = 1
                            ), 0),
                    gs.task_counter)';

        $this->getDibiConnection()->query($sql);
    }

    /**
     * @param ModelTeam $team
     * @param ModelTask $task
     * @return void
     * @throws Exception
     */
    public function updateSingleCounter(ModelTeam $team, ModelTask $task): void {
        $sql = 'UPDATE group_state AS gs
                SET task_counter = 
                    GREATEST(
                        IFNULL(
                            (
                                SELECT COUNT(id_answer)
                                FROM view_correct_answer AS ca
                                LEFT JOIN view_task tsk USING (id_task)
                                WHERE ca.id_team = gs.id_team AND tsk.id_group = gs.id_group
                             ) + (
                                SELECT COUNT(id_task)
                                FROM task_state AS ts
                                LEFT JOIN view_task tsk2 USING (id_task)
                                WHERE ts.id_team = gs.id_team AND tsk2.id_group = gs.id_group AND skipped = 1
                             ) + (
                                SELECT reserve_size
                                FROM period AS p
                                WHERE p.id_group = gs.id_group AND p.begin <= NOW() AND p.end > NOW()
                             ) + (
                                SELECT COUNT(id_task)
                                FROM view_task
                                WHERE number <= gs.task_counter AND cancelled = 1
                             ), 0),
                    gs.task_counter)
                WHERE gs.id_group = %i AND gs.id_team = %i';

        $this->getDibiConnection()->query($sql, $task->id_group, $team->id_team);
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

    protected function getTableName(): string {
        return 'tasks';
    }
}
