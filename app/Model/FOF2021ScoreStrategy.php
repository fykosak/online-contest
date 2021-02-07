<?php

namespace FOL\Model;

use FOL\Model\ORM\Models\ModelAnswer;
use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelGroup;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceAnswer;
use FOL\Model\ORM\Services\ServiceCardUsage;
use FOL\Model\ORM\Services\ServiceGroup;
use FOL\Model\ORM\Services\ServiceTeam;
use Fykosak\Utils\ORM\TypedTableSelection;

class FOF2021ScoreStrategy extends ScoreStrategy {

    private ServiceCardUsage $serviceCardUsage;
    private ServiceTeam $serviceTeam;
    private TypedTableSelection $groups;

    public function __construct(
        ServiceAnswer $serviceAnswer,
        ServiceCardUsage $serviceCardUsage,
        ServiceTeam $serviceTeam,
        ServiceGroup $serviceGroup
    ) {
        parent::__construct($serviceAnswer);
        $this->serviceCardUsage = $serviceCardUsage;
        $this->groups = $serviceGroup->getTable();
        $this->serviceTeam = $serviceTeam;
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
        $query = $task->related('answer', 'id_task')
            ->where('id_team', $team->id_team)
            ->where('correct', 0);
        /** @var ModelAnswer $correctAnswer */
        $correctAnswer = $task->related('answer', 'id_task')
            ->where('id_team', $team->id_team)
            ->where('correct', 1)
            ->fetch();

        $row = $team->related('card_usage')->where('card_type', ModelCardUsage::TYPE_RESET)->fetch();

        if ($row) {
            /** @var ModelCardUsage $usage */
            $usage = ModelCardUsage::createFromActiveRow($row);
            if ($usage->getData() == $task->id_task) {
                $query->where('inserted >= ?', $usage->created);
            }
        }
        return $this->getPoints($task, $query->count(), $correctAnswer ? $correctAnswer->double_points : false);
    }

    public function getAllBonuses(): array {
        $data = [];
        /** @var ModelTeam $team */
        foreach ($this->serviceTeam->getTable() as $team) {
            $data[$team->id_team] = $this->getBonusForTeam($team);
        }
        return $data;
    }

    public function getBonusForTeam(ModelTeam $team): int {
        /** @var ModelGroup $group */
        $bonus = 0;
        foreach ($this->groups as $group) {
            $solved = $team->getAnswers()
                ->where('task.cancelled', 0)
                ->where('answer.correct', 1)
                ->count('*');
            $all = $group->related('task')
                ->where('cancelled', 0)
                ->count('*');
            $bonus += ($solved >= $all) ? $all : 0;
        }
        return $bonus;
    }
}
