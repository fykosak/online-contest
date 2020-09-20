<?php

namespace App\Model;

use Dibi\DataSource;
use Dibi\Row;

class YearsModel extends AbstractModel {

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->getConnection()->query("SELECT * FROM [year] WHERE [id_year] = %i", $id)->fetch();
    }

    public function findCurrent(): Row {
        return $this->getConnection()->query("SELECT * FROM [view_current_year]")->fetch();
    }

    public function findAll(): DataSource {
        return $this->getConnection()->dataSource("SELECT * FROM [year]");
    }

}
