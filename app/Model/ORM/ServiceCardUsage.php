<?php

namespace FOL\Model\ORM;

use Nette\Database\Conventions;
use Nette\Database\Explorer;
use \Fykosak\Utils\ORM\AbstractService;

class ServiceCardUsage extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'card_type', ModelCardUsage::class);
    }

}
