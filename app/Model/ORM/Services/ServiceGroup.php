<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelGroup;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\ResultSet;

class ServiceGroup extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'group', ModelGroup::class);
    }

    public function findAllSkippAble(): ResultSet {
        return $this->explorer->query('
                    SELECT view_group.*
                    FROM view_group
                    RIGHT JOIN period ON period.id_group = view_group.id_group
                        AND period.begin <= NOW() AND period.end > NOW()
                    WHERE
                        to_show < NOW()
                        AND period.allow_skip = 1
                    ORDER BY id_group');
    }
}