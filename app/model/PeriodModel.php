<?php

namespace App\Model;

class PeriodModel extends AbstractModel {

    public function find($id) {
        throw new NotSupportedException();
    }

    public function findAll() {
        throw new NotSupportedException();
    }

    public function findCurrent($groupId) {
        $source = $this->getConnection()->dataSource("SELECT * FROM [period]");
        $source->where("[id_group] = %i", $groupId);
        $source->where("[begin] <= NOW() AND [end] > NOW()");
        return $source->fetch();
    }
    
}