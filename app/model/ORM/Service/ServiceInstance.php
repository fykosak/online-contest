<?php

namespace FOL\Model\ORM\Service;

use FOL\Model\ORM\Model\ModelInstance;
use Nette\Database\Context;
use Nette\Database\IConventions;

class ServiceInstance extends AbstractService {
    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, 'instance', ModelInstance::class);
    }
}
