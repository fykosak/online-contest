<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelGroup;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServiceGroup extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'group', ModelGroup::class);
    }
}
