<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelAnswer;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServiceAnswer extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'answer', ModelAnswer::class);
    }
}
