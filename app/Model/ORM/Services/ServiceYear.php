<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelYear;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceYear extends AbstractService {

    private ModelYear $year;

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'year', ModelYear::class);
    }

    public function getCurrent(): ModelYear {
        if (!isset($this->year)) {
            $this->year = $this->getTable()->fetch();
        }
        return $this->year;
    }

    public function isGameMigrated(): bool {
        return $this->getCurrent()->isRegistrationEnd() && ($this->context->table('team')->count() != 0);
    }
}
