<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelTask;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceTask extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'task', ModelTask::class);
    }

}
