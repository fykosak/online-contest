<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelCardUsage;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Fykosak\Utils\ORM\AbstractService;

final class ServiceCardUsage extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'card_usage', ModelCardUsage::class);
    }
}
