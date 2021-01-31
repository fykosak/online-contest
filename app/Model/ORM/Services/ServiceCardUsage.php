<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelTeam;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Fykosak\Utils\ORM\AbstractService;

final class ServiceCardUsage extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'card_usage', ModelCardUsage::class);
    }

    public function findByTypeAndTeam(ModelTeam $team, string $type): ?ModelCardUsage {
        /** @var ModelCardUsage|null $results */
        $results = $this->getTable()->where('team_id', $team->id_team)->where('card_type', $type)->fetch();
        return $results;
    }
}
