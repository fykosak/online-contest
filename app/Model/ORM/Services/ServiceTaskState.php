<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelTaskState;
use FOL\Model\ORM\Models\ModelTeam;
use Fykosak\Utils\ORM\AbstractService;
use Fykosak\Utils\ORM\TypedTableSelection;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceTaskState extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'task_state', ModelTaskState::class);
    }

    public function findFormTeam(ModelTeam $team): TypedTableSelection {
        return $this->getTable()->where('id_team', $team->id_team);
    }

    public function findSolved(ModelTeam $team): TypedTableSelection {
        return $this->findFormTeam($team)->where('points IS NOT NULL');
    }

    public function findSkipped(ModelTeam $team): TypedTableSelection {
        return $this->findFormTeam($team)->where('skipped = 1');
    }
}
