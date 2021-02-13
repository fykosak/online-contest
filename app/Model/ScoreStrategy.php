<?php

namespace FOL\Model;

use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceAnswer;
use Nette\SmartObject;

abstract class ScoreStrategy {

    use SmartObject;

    protected ServiceAnswer $serviceAnswer;

    public function __construct(ServiceAnswer $serviceAnswer) {
        $this->serviceAnswer = $serviceAnswer;
    }

    public function getSingleTaskScore(ModelTeam $team, ModelTask $task): int {
        $query = $task->related('answer', 'id_task')
            ->where('id_team', $team->id_team)
            ->where('correct', 0);

        return $this->getPoints($task, $query->count());
    }

    abstract protected function getPoints(ModelTask $task, int $wrongTries): int;

}
