<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelYear;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceYear extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'year', ModelYear::class);
    }

    public function getCurrent(): ModelYear {
        /** @var ModelYear $year */
        $year = $this->getTable()->fetch();
        return $year;
    }

    public function isGameMigrated(): bool {
        return $this->getCurrent()->isRegistrationEnd() && ($this->context->table('team')->count() != 0);
    }
}
