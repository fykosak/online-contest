<?php

namespace App\Model;

use DateTime;

class SchoolsModel extends AbstractModel {

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_school] = %i", $id)->fetch();
    }

    public function findAll() {
        return $this->getConnection()->dataSource("SELECT * FROM [school]");
    }

    public function insert($name) {
        $this->checkEmptiness($name, "name");
        $this->getConnection()->insert("school", [
            "name" => $name,
            "inserted" => new DateTime(),
        ])->execute();
        $return = $this->getConnection()->insertId();
        $this->log(null, "school_inserted", "The school [$name] has been inserted.");
        return $return;
    }

}
