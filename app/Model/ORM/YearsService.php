<?php

namespace FOL\Model\ORM;

use Dibi\Connection as DibiConnection;
use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Row;
use FOL\Model\ORM\Services\ServiceYear;
use Nette\Database\Explorer;
use Nette\DeprecatedException;

class YearsService extends AbstractService {

    private ServiceYear $serviceYear;

    public function __construct(ServiceYear $serviceYear, Explorer $explorer, DibiConnection $dibiConnection) {
        parent::__construct($explorer, $dibiConnection);
        $this->serviceYear = $serviceYear;
    }

    public function find(int $id): ?Row {
        throw new DeprecatedException();
    }

    public function findAll(): DataSource {
        throw new DeprecatedException();
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
