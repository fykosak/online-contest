<?php

namespace App\Model;

use Dibi\DataSource;

class GroupsModel extends AbstractModel {

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_group] = %i", $id)->fetch();
    }

    public function findAll(): DataSource {
        return $this->getConnection()->dataSource("SELECT * FROM [view_group]");
    }

    public function findAllAvailable(): DataSource {
        return $this->getConnection()->dataSource("SELECT * FROM [view_group] WHERE [to_show] < NOW() ORDER BY [id_group]");
    }

    public function findAllSkippable(): DataSource {
        $source = $this->getConnection()->dataSource("
                    SELECT [view_group].*
                    FROM [view_group]
                    RIGHT JOIN [period] ON [period].[id_group] = [view_group].[id_group]
                        AND [period].[begin] <= NOW() AND [period].[end] > NOW()
                    WHERE
                        [to_show] < NOW()
                        AND [period].[allow_skip] = 1
                    ORDER BY [id_group]");
        return $source;
    }

}
