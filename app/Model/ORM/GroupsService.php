<?php

namespace FOL\Model\ORM;

use Nette\Database\ResultSet;

class GroupsService extends AbstractService {

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
