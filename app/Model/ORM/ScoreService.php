<?php

namespace FOL\Model\ORM;

use DateTime;
use Exception;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceGroup;
use FOL\Model\ORM\Services\ServiceLog;
use FOL\Model\ORM\Services\ServicePeriod;
use FOL\Model\ORM\Services\ServiceTaskState;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Model\ScoreStrategy;
use Nette\Database\Explorer;
use Tracy\Debugger;

final class ScoreService extends AbstractService {

    private ServicePeriod $servicePeriod;
    private ServiceTaskState $serviceTaskState;
    private ScoreStrategy $scoreStrategy;
    private ServiceTeam $serviceTeam;
    private ServiceGroup $serviceGroup;

    public function __construct(
        Explorer $explorer,
        ServicePeriod $servicePeriod,
        ServiceLog $serviceLog,
        ServiceTaskState $serviceTaskState,
        ScoreStrategy $scoreStrategy,
        ServiceTeam $serviceTeam,
        ServiceGroup $serviceGroup
    ) {
        parent::__construct($explorer, $serviceLog);
        $this->servicePeriod = $servicePeriod;
        $this->serviceTaskState = $serviceTaskState;
        $this->scoreStrategy = $scoreStrategy;
        $this->serviceGroup = $serviceGroup;
        $this->serviceTeam = $serviceTeam;
    }

    public function updateAfterInsert(ModelTeam $team, ModelTask $task): void {
        try {
            $score = $this->scoreStrategy->getSingleTaskScore($team, $task);
            $this->serviceTaskState->createNewModel([
                'id_team' => $team->id_team,
                'id_task' => $task->id_task,
                'inserted' => new DateTime(),
                'skipped' => 0,
                'points' => $score,
            ]);
            $counter = 0;
            $answers = $team->related('task_state')->where('task.id_group', $task->id_group);
            $bonusScore = 0;
            foreach ($answers as $row) {
                $counter++;
                if (!$row->skipped) {
                    $bonusScore++;
                }
            }
            if ($counter === $task->getGroup()->related('task')->count('*')) {
                $score += $bonusScore;
            }
            $this->explorer->query('UPDATE team SET score_exp = score_exp + ?', $score, 'WHERE id_team = ?', $team->id_team);
        } catch (Exception $e) {
            Debugger::log($e);
        }
    }
}
