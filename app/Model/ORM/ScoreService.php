<?php

namespace FOL\Model\ORM;

use DateTime;
use Exception;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceLog;
use FOL\Model\ORM\Services\ServiceTaskState;
use FOL\Model\ScoreStrategy;
use Nette\Database\Explorer;
use Tracy\Debugger;

final class ScoreService extends AbstractService {

    private ServiceTaskState $serviceTaskState;
    private ScoreStrategy $scoreStrategy;

    public function __construct(
        Explorer $explorer,
        ServiceLog $serviceLog,
        ServiceTaskState $serviceTaskState,
        ScoreStrategy $scoreStrategy
    ) {
        parent::__construct($explorer, $serviceLog);
        $this->serviceTaskState = $serviceTaskState;
        $this->scoreStrategy = $scoreStrategy;
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
            $answers = $team->getTaskState()->where('task.id_group', $task->id_group);
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
