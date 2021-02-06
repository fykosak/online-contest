<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelRating;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceRating extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'rating', ModelRating::class);
    }
}
