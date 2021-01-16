<?php

namespace FOL\Model\ORM;

use DateTime;
use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Row;

class SchoolsService extends AbstractService {
    /**
     * @param $id
     * @return Row|false
     * @throws Exception
     */
    public function find($id) {
        return $this->findAll()->where("[id_school] = %i", $id)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAll() {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [school]");
    }

    /**
     * @param $name
     * @return int
     * @throws Exception
     */
    public function insert($name) {
        $this->getDibiConnection()->insert("school", [
            "name" => $name,
            "inserted" => new DateTime(),
        ])->execute();
        $return = $this->getDibiConnection()->insertId();
        $this->log(null, "school_inserted", "The school [$name] has been inserted.");
        return $return;
    }

    protected function getTableName(): string {
        return 'schools';
    }
}
