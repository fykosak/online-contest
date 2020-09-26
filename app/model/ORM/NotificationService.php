<?php

namespace FOL\Model\ORM;

use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Row;

class NotificationService extends AbstractService {
    /**
     * @param $id
     * @return Row|false
     * @throws Exception
     */
    public function find($id) {
        return $this->findAll()->where("[id_notification] = %i", $id)->fetch();
    }

    /**
     * @param null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findAll($lang = null): DataSource {
        $dataSource = $this->getDibiConnection()->dataSource("SELECT * FROM [notification]");

        if ($lang !== null) {
            return $dataSource->where("[lang] = %s", $lang);
        }
        return $dataSource;
    }

    /**
     * @param null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findActive($lang = null): DataSource {
        return $this->findAll($lang)->where("[created] < NOW()")->orderBy('created', 'DESC');
    }

    /**
     * @param $timestamp
     * @param null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findNew($timestamp, $lang = null): DataSource {
        return $this->findActive($lang)->where("[created] > %t", $timestamp)->orderBy('created');
    }

    /**
     * @param $message
     * @param $lang
     * @return void
     * @throws Exception
     */
    public function insert($message, $lang) {
        $this->getDibiConnection()->insert("notification", [
            'message' => $message,
            'lang' => $lang,
        ])->execute();
    }

    /**
     * @param $messageCs
     * @param $messageEn
     * @return void
     * @throws Exception
     */
    public function insertNotification($messageCs, $messageEn) {
        $connection = $this->getDibiConnection();
        $connection->insert("notification", [
            'message' => $messageCs,
            'lang' => 'cs',
        ])->execute();
        $connection->insert("notification", [
            'message' => $messageEn,
            'lang' => 'en',
        ])->execute();
    }

    protected function getTableName(): string {
        return 'notification';
    }
}
