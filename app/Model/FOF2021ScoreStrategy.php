<?php

namespace FOL\Model;

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

    protected function getPoints(ModelTask $task, int $wrongTries): int {
        switch ($wrongTries) {
            case 0:
                return 5;
            case 1:
                return 3;
            case 2:
                return 2;
            default:
                return 1;
        }
    }

    public function getSingleTaskScore(ModelTeam $team, ModelTask $task): int {
        $query = $this->serviceAnswer->getTable()
            ->where('id_team', $team->id_team)
            ->where('id_task', $task->id_task)
            ->where('correct', 0);

        /** @var ModelCardUsage|null $usage */
        $usage = $this->serviceCardUsage->where('team_id', $team->id_team)->where('type', 'reset')->fetch();
        if ($usage) {
            $taskId = $usage->getData()['task'];
            if ($taskId == $task->id_task) {
                $query->where('created >= ?', $usage->created);
            }
        }
        return $this->getPoints($task, $query->count());
    }
}
