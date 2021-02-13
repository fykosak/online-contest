<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelTaskState;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServiceTaskState extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'task_state', ModelTaskState::class);
    }
}
