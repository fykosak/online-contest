<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelTaskHint;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServiceTaskHint extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'task_hint', ModelTaskHint::class);
    }

    public function getTaskHint(int $taskId): ?ModelTaskHint {
        /** @var ModelTaskHint|null $results */
        $results = $this->findByPrimary($taskId);
        return $results;
    }
}
