<?php

namespace App\Model;

use DateTime;
use Dibi\Connection;
use Exception;
use Nette\SmartObject;
use Tracy\Debugger;

abstract class AbstractModel implements InterlosModel {
    use SmartObject;

    private Connection $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    // ----- PROTECTED METHODS

    protected final function checkEmptiness($var, $name): void {
//		if (empty($var)) {
//			throw new NullPointerException("The parameter [$name] is empty.");
//		}
    }

    protected final function getConnection(): Connection {
        return $this->connection;
    }

    protected final function log($team, $type, $text) {
        try {
            $this->getConnection()->insert("log", [
                "id_team" => $team,
                "type" => $type,
                "text" => $text,
                "inserted" => new DateTime(),
            ])->execute();
        } catch (Exception $e) {
            Debugger::log($e);
        }
    }

}
