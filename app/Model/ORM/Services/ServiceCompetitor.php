<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelCompetitor;
use FOL\Model\ORM\Models\ModelTeam;
use Fykosak\Utils\ORM\AbstractService;
use Fykosak\Utils\ORM\TypedTableSelection;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServiceCompetitor extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'competitor', ModelCompetitor::class);
    }

    public function findAllByTeam(ModelTeam $team): TypedTableSelection {
        return $this->getTable()->where('id_team', $team->id_team);
    }

    public function findByEmail(string $email): ?ModelCompetitor {
        /** @var ModelCompetitor $competitor */
        $competitor = $this->getTable()->where('email', $email)->fetch();
        return $competitor;
    }
}
