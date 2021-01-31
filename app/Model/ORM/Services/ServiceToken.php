<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelToken;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServiceToken extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'token', ModelToken::class);
    }
}
