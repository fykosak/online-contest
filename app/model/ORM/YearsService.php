<?php

namespace FOL\Model\ORM;

use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Row;

class YearsService extends AbstractService {
    /**
     * @param $id
     * @return array|Row|false
     * @throws Exception
     */
    public function find($id) {
        return $this->getDibiConnection()->query("SELECT * FROM [year] WHERE [id_year] = %i", $id)->fetch();
    }

    /**
     * @return Row
     * @throws Exception
     */
    public function findCurrent(): Row {
        return $this->getDibiConnection()->query("SELECT * FROM [view_current_year]")->fetch();
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    public function findAll(): DataSource {
        return $this->getDibiConnection()->dataSource("SELECT * FROM [year]");
    }

    protected function getTableName(): string {
        return 'years';
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isGameActive() {
        return $this->isGameStarted() && !$this->isGameEnd();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isGameEnd(): bool {
        return time() > strtotime($this->findCurrent()->game_end);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isGameStarted(): bool {
        return strtotime($this->findCurrent()->game_start) < time();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isRegistrationActive(): bool {
        return self::isRegistrationStarted() && !self::isRegistrationEnd();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isRegistrationEnd(): bool {
        return strtotime($this->findCurrent()->registration_end) < time();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isRegistrationStarted(): bool {
        return strtotime($this->findCurrent()->registration_start) < time();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isGameMigrated() {
        return $this->isRegistrationEnd() && ($this->getDibiConnection()->dataSource("SELECT COUNT(*) FROM [team]")->fetchSingle() != 0);
    }

}
