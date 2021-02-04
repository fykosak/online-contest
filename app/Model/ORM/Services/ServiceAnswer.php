<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelAnswer;
use FOL\Model\ORM\Models\ModelTeam;
use Fykosak\Utils\ORM\AbstractService;
use Fykosak\Utils\ORM\TypedTableSelection;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServiceAnswer extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'answer', ModelAnswer::class);
    }

    public function findAllCorrect(ModelTeam $team): TypedTableSelection {
        return $this->getTable()
            ->where('correct', 1)
            ->where('task.cancelled', 0)
            ->where('id_team', $team->id_team);
    }

    public function getCorrectedAnswer(): TypedTableSelection {
        return $this->getTable()
            ->where('task.cancelled', 0)
            ->where('answer.correct', 1);
    }

    public function getLastCorrectAnswer(ModelTeam $team): ?ModelAnswer {
        return $this->getCorrectedAnswer()
            ->where('id_team', $team->id_team)
            ->fetch();
    }
}
