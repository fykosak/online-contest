<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelCompetitor;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServiceCompetitor extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'competitor', ModelCompetitor::class);
    }

    public function findByEmail(string $email): ?ModelCompetitor {
        /** @var ModelCompetitor $competitor */
        $competitor = $this->getTable()->where('email', $email)->fetch();
        return $competitor;
    }
}
