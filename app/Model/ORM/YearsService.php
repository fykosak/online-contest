<?php

namespace FOL\Model\ORM;

use Dibi\Connection as DibiConnection;
use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Row;
use FOL\Model\ORM\Services\ServiceYear;
use Nette\Database\Explorer;

class YearsService extends AbstractService {

    private ServiceYear $serviceYear;

    public function __construct(ServiceYear $serviceYear, Explorer $explorer, DibiConnection $dibiConnection) {
        parent::__construct($explorer, $dibiConnection);
        $this->serviceYear = $serviceYear;
    }

    /**
     * @param $id
     * @return array|Row|null
     * @throws Exception
     */
    public function find($id) {
        return $this->getDibiConnection()->query('SELECT * FROM [year] WHERE [id_year] = %i', $id)->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAll(): DataSource {
        return $this->getDibiConnection()->dataSource('SELECT * FROM [year]');
    }

    protected function getTableName(): string {
        return 'years';
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isGameMigrated(): bool {
        return $this->serviceYear->getCurrent()->isRegistrationEnd() && ($this->getDibiConnection()->dataSource('SELECT COUNT(*) FROM [team]')->fetchSingle() != 0);
    }
}
