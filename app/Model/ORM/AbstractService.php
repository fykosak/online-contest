<?php

namespace FOL\Model\ORM;

use DateTime;
use Exception;
use Dibi\Connection as DibiConnection;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\SmartObject;
use Tracy\Debugger;

abstract class AbstractService implements IService {
    use SmartObject;

    private Explorer $explorer;
    private DibiConnection $dibiConnection;

    public function __construct(Explorer $explorer, DibiConnection $dibiConnection) {
        $this->explorer = $explorer;
        $this->dibiConnection = $dibiConnection;
    }

    protected final function getConnection(): Explorer {
        return $this->explorer;
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
        return $this->getConnection()->table($this->getTableName());
    }

    abstract protected function getTableName(): string;
}
