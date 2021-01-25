<?php

namespace FOL\Model;

use FOL\Model\ORM\Models\ModelGroup;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceAnswer;
use FOL\Model\ORM\Services\ServiceGroup;
use FOL\Model\ORM\Services\ServiceLog;
use FOL\Model\ORM\Services\ServicePeriod;
use FOL\Model\ORM\Services\ServiceTaskState;
use Nette\Database\Explorer;
use Nette\SmartObject;

abstract class ScoreStrategy {

    use SmartObject;

    private ServiceGroup $serviceGroup;
    private ServicePeriod $servicePeriod;
    private ServiceTaskState $serviceTaskState;
    private ServiceAnswer $serviceAnswer;
    private ServiceLog $serviceLog;
    protected Explorer $explorer;

    public function __construct(
        Explorer $explorer,
        ServicePeriod $servicePeriod,
        ServiceGroup $serviceGroup,
        ServiceLog $serviceLog,
        ServiceTaskState $serviceTaskState,
        ServiceAnswer $serviceAnswer
    ) {
        $this->explorer = $explorer;
        $this->serviceLog = $serviceLog;
        $this->serviceGroup = $serviceGroup;
        $this->servicePeriod = $servicePeriod;
        $this->serviceTaskState = $serviceTaskState;
        $this->serviceAnswer = $serviceAnswer;
    }

    public function getSingleTaskScore(ModelTeam $team, ModelTask $task): int {
        $wrongTries = $this->serviceAnswer->getTable()
            ->where('id_team', $team->id_team)
            ->where('id_task', $task->id_task)
            ->where('correct', 0)->count();

        return $this->getPoints($task, $wrongTries);
    }

    abstract protected function getPoints(ModelTask $task, int $wrongTries): int;

}
