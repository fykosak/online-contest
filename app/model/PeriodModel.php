<?php

namespace App\Model;

use Nette;

class PeriodModel extends AbstractModel {

    public function find($id) {
        throw new Nette\NotSupportedException();
    }

    public function findAll() {
        throw new Nette\NotSupportedException();
    }

    public function findCurrent($groupId) {
        $source = $this->getConnection()->dataSource("SELECT * FROM [period]");
        $source->where("[id_group] = %i", $groupId);
        $source->where("[begin] <= NOW() AND [end] > NOW()");
        return $source->fetch();
    }
    
}