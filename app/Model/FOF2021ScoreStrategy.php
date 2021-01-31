<?php

namespace FOL\Model;

use FOL\Model\ORM\Models\ModelAnswer;
use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceAnswer;
use FOL\Model\ORM\Services\ServiceCardUsage;
use FOL\Model\ORM\Services\ServiceLog;

class FOF2021ScoreStrategy extends ScoreStrategy {

    private ServiceCardUsage $serviceCardUsage;

    public function __construct(
        ServiceLog $serviceLog,
        ServiceAnswer $serviceAnswer,
        ServiceCardUsage $serviceCardUsage
    ) {
        parent::__construct($serviceLog, $serviceAnswer);
        $this->serviceCardUsage = $serviceCardUsage;
    }

    protected function getPoints(ModelTask $task, int $wrongTries, bool $hasDoublePoints = false): int {
        $rawPoints = null;
        switch ($wrongTries) {
            case 0:
                $rawPoints = 5;
                break;
            case 1:
                $rawPoints = 3;
                break;
            case 2:
                $rawPoints = 2;
                break;
            default:
                $rawPoints = 1;
        }
        return $hasDoublePoints ? $rawPoints * 2 : $rawPoints;
    }

    public function getSingleTaskScore(ModelTeam $team, ModelTask $task): int {
        $query = $this->serviceAnswer->getTable()
            ->where('id_team', $team->id_team)
            ->where('id_task', $task->id_task)
            ->where('correct', 0);
        /** @var ModelAnswer $correctAnswer */
        $correctAnswer = $this->serviceAnswer->getTable()
            ->where('id_team', $team->id_team)
            ->where('id_task', $task->id_task)
            ->where('correct', 1)
            ->fetch();
        $usage = $this->serviceCardUsage->findByTypeAndTeam($team, ModelCardUsage::TYPE_RESET);
        if ($usage) {
            $taskId = $usage->getData()['task'];
            if ($taskId == $task->id_task) {
                $query->where('inserted >= ?', $usage->created);
            }
        }
        return $this->getPoints($task, $query->count(), $correctAnswer ? $correctAnswer->double_points : false);
    }
}
