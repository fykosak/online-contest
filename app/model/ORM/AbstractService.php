<?php

namespace FOL\Model\ORM;

use DateTime;
use Exception;
use Dibi\Connection as DibiConnection;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\SmartObject;
use Tracy\Debugger;

abstract class AbstractService implements IService {
    use SmartObject;

    private Context $connection;
    private DibiConnection $dibiConnection;

    public function __construct(Context $connection, DibiConnection $dibiConnection) {
        $this->connection = $connection;
        $this->dibiConnection = $dibiConnection;
    }

    protected final function getConnection(): Context {
        return $this->connection;
    }

    protected final function getDibiConnection(): DibiConnection {
        return $this->dibiConnection;
    }

    protected final function log($team, $type, $text): void {
        try {
            $this->getDibiConnection()->insert("log", [
                "id_team" => $team,
                "type" => $type,
                "text" => $text,
                "inserted" => new DateTime(),
            ])->execute();
        } catch (Exception $e) {
            Debugger::log($e);
        }
    }

    public function findByPrimary(int $primaryKey): ?ActiveRow {
        $result = $this->getConnection()->table($this->getTableName())->wherePrimary($primaryKey)->fetch();
        return $result ? $result : null;
    }

    protected function getAll(): Selection {
        return $result = $this->getConnection()->table($this->getTableName());
    }

    abstract protected function getTableName(): string;
}
