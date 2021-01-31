<?php

namespace FOL\Model\ORM;

use DateTime;
use Exception;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceLog;
use FOL\Model\ORM\Services\ServicePeriod;
use FOL\Model\ORM\Services\ServiceTaskState;
use FOL\Model\ScoreStrategy;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Tracy\Debugger;

class ScoreService extends AbstractService {

    private ServicePeriod $servicePeriod;
    private ServiceTaskState $serviceTaskState;
    private ScoreStrategy $scoreStrategy;

    public function __construct(
        Explorer $explorer,
        ServicePeriod $servicePeriod,
        ServiceLog $serviceLog,
        ServiceTaskState $serviceTaskState,
        ScoreStrategy $scoreStrategy
    ) {
        parent::__construct($explorer, $serviceLog);
        $this->servicePeriod = $servicePeriod;
        $this->serviceTaskState = $serviceTaskState;
        $this->scoreStrategy = $scoreStrategy;
    }

    public function findAllBonus(): Selection {
        return $this->explorer->table('tmp_bonus');
    }

    public function findAllPenality(): Selection {
        return $this->explorer->table('tmp_penality');
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
// TODO
            /* vypocet bonusu */
            /*    if ($hurry) {
                    $solvedTasks = $this->serviceTaskState->findSolved($team);
                    $hurryTasks = $solvedTasks->where('id_task IN', $solvedTasks)
                        ->where('task.number', $task->number)
                        ->where('task.id_group <> 1');
                    if (count($hurryTasks) == 3 && $this->servicePeriod->findCurrent($task->getGroup())->has_bonus == 1) {
                        /** @var ModelTaskState $hurryTask
                        foreach ($hurryTasks as $hurryTask) {
                            $score += $this->scoreStrategy->getSingleTaskScore($team, $hurryTask->getTask());
                        }
                    }
                }*/
            $this->explorer->query('UPDATE team SET score_exp = score_exp + ?', $score, 'WHERE id_team = ?', $team->id_team);
        } catch (Exception $e) {
            Debugger::log($e);
        }
    }
}
