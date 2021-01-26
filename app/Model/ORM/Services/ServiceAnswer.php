<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelAnswer;
use Fykosak\Utils\ORM\AbstractService;
use Fykosak\Utils\ORM\TypedTableSelection;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceAnswer extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'answer', ModelAnswer::class);
    }

    public function findByTaskId(int $taskId): TypedTableSelection {
        return $this->getTable()->where('id_task', $taskId);
    }
}
