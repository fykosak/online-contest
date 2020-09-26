<?php

namespace FOL\Model\ORM;

use Dibi\Exception;
use Dibi\Row;
use Nette\NotSupportedException;

class PeriodService extends AbstractService {

    public function find($id) {
        throw new NotSupportedException();
    }

    public function findAll() {
        throw new NotSupportedException();
    }

    /**
     * @param $groupId
     * @return Row|false
     * @throws Exception
     */
    public function findCurrent($groupId) {
        $source = $this->getDibiConnection()->dataSource("SELECT * FROM [period]");
        $source->where("[id_group] = %i", $groupId);
        $source->where("[begin] <= NOW() AND [end] > NOW()");
        return $source->fetch();
    }

    protected function getTableName(): string {
        return 'period';
    }
}
