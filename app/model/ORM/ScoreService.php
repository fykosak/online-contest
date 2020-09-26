<?php

namespace FOL\Model\ORM;

use DateTime;
use Dibi\Connection as DibiConnection;
use Dibi\DataSource;
use Exception;
use Nette\Database\Context;
use Nette\NotSupportedException;
use Tracy\Debugger;

class ScoreService extends AbstractService {

    protected PeriodService $periodService;
    protected TasksService $tasksService;
    protected GroupsService $groupsService;

    public function __construct(
        Context $connection,
        DibiConnection $dibiConnection,
        PeriodService $periodService,
        TasksService $tasksService,
        GroupsService $groupsService
    ) {
        parent::__construct($connection, $dibiConnection);
        $this->periodService = $periodService;
        $this->tasksService = $tasksService;
        $this->groupsService = $groupsService;
    }

    public function find($id) {
        throw new NotSupportedException();
    }

    public function findAll() {
        throw new NotSupportedException();
    }

    /**
     * @return DataSource
     * @throws \Dibi\Exception
     */
    public function findAllBonus(): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [tmp_bonus]");
    }

    /**
     * @return DataSource
     * @throws \Dibi\Exception
     */
    public function findAllTasks(): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [task_state]");
    }

    /**
     * @return DataSource
     * @throws \Dibi\Exception
     */
    public function findAllPenality(): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [tmp_penality]");
    }

    /**
     * @return DataSource
     * @throws \Dibi\Exception
     */
    public function findAllSkips(): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [task_state] WHERE skipped = 1");
    }

    /**
     * @param $teamId
     * @return void
     * @throws \Dibi\Exception
     */
    public function updateAfterSkip($teamId) {
        $this->getDibiConnection()->query("UPDATE [team] SET score_exp = score_exp-1 WHERE id_team = %i", $teamId);
    }

    public function updateAfterCancel($task) {
        //TODO
    }

    public function updateAfterInsert($teamId, $task) {
        try {
            $hurry = ($task->id_group == 1) ? false : true; //dle SQL id_group=2,3,4

            $score = $this->getSingleTaskScore($teamId, $task);
            $this->getDibiConnection()->insert("task_state", [
                "id_team" => $teamId,
                "id_task" => $task->id_task,
                "inserted" => new DateTime(),
                "skipped" => 0,
                "points" => $score,])->execute();

            /* vypocet bonusu */
            if ($hurry) {
                $solvedTasks = $this->tasksService->findSolved($teamId);
                $hurryTasks = $this->tasksService->findAll()
                    ->where("[id_task] IN %l", $solvedTasks)
                    ->where("[number] = %i", $task->number)
                    ->where("[id_group] <> 1")->fetchAll();
                if (count($hurryTasks) == 3 && $this->periodService->findCurrent($task->id_group)->has_bonus == 1) {
                    foreach ($hurryTasks as $hurryTask) {
                        $score += $this->getSingleTaskScore($teamId, $hurryTask);
                    }
                }
            }

            $this->getDibiConnection()->query("UPDATE [team] SET score_exp = score_exp + %i", $score, "WHERE id_team = %i", $teamId);
        } catch (Exception $e) {
            Debugger::log($e);
        }
    }

    /**
     * @param $teamId
     * @param $task
     * @return int|mixed
     * @throws \Dibi\Exception
     */
    public function getSingleTaskScore($teamId, $task) {
        $group = $this->groupsService->find($task->id_group);
        $wrongTries = $this->getDibiConnection()->query("SELECT COUNT(*) FROM [answer] WHERE %and", [
            ['id_team = %i', $teamId],
            ['id_task = %i', $task->id_task],
            ['correct = %i', 0],
        ])->fetchSingle();

        return $this->getPointCount($task->points, $wrongTries, $group->allow_zeroes);
    }

    private function getPointCount($maxPoints, $wrongTries, $allowZeroes) {
        $score = 0;

        if (!$this->isHurryUp($allowZeroes)) {
            switch ($wrongTries) {
                case 0:
                    $score = $maxPoints;
                    break;
                case 1:
                    $score = ceil(0.6 * $maxPoints);
                    break;
                case 2:
                    $score = ceil(0.4 * $maxPoints);
                    break;
                case 3:
                    $score = ceil(0.2 * $maxPoints);
                    break;
                default:
                    $score = 0;
                    break;
            }
        } elseif ($maxPoints == 0) {
            return 0;
        } else {
            $score = $maxPoints - $wrongTries;
        }

        return ($allowZeroes) ? max(0, $score) : max(1, $score);
    }

    /**
     * Checks if task belongs to hurry up
     *
     * @param bool $allowZeroes
     * @return bool
     */
    private function isHurryUp($allowZeroes) {
        return $allowZeroes;
    }

    protected function getTableName(): string {
        return 'schools';
    }
}
