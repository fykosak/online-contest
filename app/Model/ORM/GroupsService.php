<?php

namespace FOL\Model\ORM;

use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Row;

class GroupsService extends AbstractService {

    /**
     * @param $id
     * @return Row|null
     * @throws Exception
     */
    public function find(int $id): ?Row {
        return $this->findAll()->where('[id_group] = %i', $id)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAll(): DataSource {
        return $this->getDibiConnection()->dataSource('SELECT * FROM [view_group]');
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAllAvailable(): DataSource {
        return $this->getDibiConnection()->dataSource('SELECT * FROM [view_group] WHERE [to_show] < NOW() ORDER BY [id_group]');
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAllSkippable(): DataSource {
        return $this->getDibiConnection()->dataSource('
                    SELECT [view_group].*
                    FROM [view_group]
                    RIGHT JOIN [period] ON [period].[id_group] = [view_group].[id_group]
                        AND [period].[begin] <= NOW() AND [period].[end] > NOW()
                    WHERE
                        [to_show] < NOW()
                        AND [period].[allow_skip] = 1
                    ORDER BY [id_group]');
    }

    protected function getTableName(): string {
        return 'groups';
    }

}
