<?php

namespace FOL\Model\ORM;

use DateTime;
use Exception;
use FOL\Model\ORM\Models\ModelGroup;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTaskState;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceAnswer;
use FOL\Model\ORM\Services\ServiceGroup;
use FOL\Model\ORM\Services\ServiceLog;
use FOL\Model\ORM\Services\ServicePeriod;
use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ORM\Services\ServiceTaskState;
use FOL\Model\ScoreStrategy;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Tracy\Debugger;

class ScoreService extends AbstractService {

    private ServiceGroup $serviceGroup;
    private ServicePeriod $servicePeriod;
    private ServiceTaskState $serviceTaskState;
    private ServiceAnswer $serviceAnswer;
    private ScoreStrategy $scoreStrategy;
    private ServiceTask $serviceTask;

    public function __construct(
        Explorer $explorer,
        ServicePeriod $servicePeriod,
        ServiceGroup $serviceGroup,
        ServiceLog $serviceLog,
        ServiceTaskState $serviceTaskState,
        ServiceAnswer $serviceAnswer,
        ScoreStrategy $scoreStrategy,
        ServiceTask $serviceTask
    ) {
        parent::__construct($explorer, $serviceLog);
        $this->serviceGroup = $serviceGroup;
        $this->servicePeriod = $servicePeriod;
        $this->serviceTaskState = $serviceTaskState;
        $this->serviceAnswer = $serviceAnswer;
        $this->scoreStrategy = $scoreStrategy;
        $this->serviceTask = $serviceTask;
    }

    public function findAllBonus(): Selection {
        return $this->explorer->table('tmp_bonus');
    }

    public function findAllPenality(): Selection {
        return $this->explorer->table('tmp_penality');
    }

    public function updateAfterSkip(ModelTeam $team): void {
        $this->explorer->query('UPDATE team SET score_exp = score_exp-1 WHERE id_team = ?', $team->id_team);
    }

    public function updateAfterInsert(ModelTeam $team, ModelTask $task): void {
        try {
            $hurry = ($task->id_group == 1) ? false : true; //dle SQL id_group=2,3,4

            $score = $this->scoreStrategy->getSingleTaskScore($team, $task);
            $this->explorer->table('task_state')->insert([
                'id_team' => $team->id_team,
                'id_task' => $task->id_task,
                'inserted' => new DateTime(),
                'skipped' => 0,
                'points' => $score,]);

            /* vypocet bonusu */
            if ($hurry) {
                $solvedTasks = $this->serviceTaskState->findSolved($team);
                $hurryTasks = $solvedTasks->where('id_task IN', $solvedTasks)
                    ->where('task.number', $task->number)
                    ->where('task.id_group <> 1');
                if (count($hurryTasks) == 3 && $this->servicePeriod->findCurrent($task->getGroup())->has_bonus == 1) {
                    /** @var ModelTaskState $hurryTask */
                    foreach ($hurryTasks as $hurryTask) {
                        $score += $this->scoreStrategy->getSingleTaskScore($team, $hurryTask->getTask());
                    }
                }
            }
            $this->explorer->query('UPDATE team SET score_exp = score_exp + ?', $score, 'WHERE id_team = ?', $team->id_team);
        } catch (Exception $e) {
            Debugger::log($e);
        }
    }
}
