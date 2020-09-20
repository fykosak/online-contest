<?php

namespace App\Model;

use Dibi\DataSource;

class NotificationModel extends AbstractModel {

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_notification] = %i", $id)->fetch();
    }

    public function findAll($lang = null): DataSource {
        $dataSource = $this->getConnection()->dataSource("SELECT * FROM [notification]");

        if ($lang !== null) {
            return $dataSource->where("[lang] = %s", $lang);
        }
        return $dataSource;
    }


    public function findActive($lang = null): DataSource {
        return $this->findAll($lang)->where("[created] < NOW()")->orderBy('created', 'DESC');
    }

    public function findNew($timestamp, $lang = null): DataSource {
        return $this->findActive($lang)->where("[created] > %t", $timestamp)->orderBy('created');
    }

    public function insert($message, $lang) {
        $this->getConnection()->insert("notification", [
            'message' => $message,
            'lang' => $lang,
        ])->execute();
    }

    public function insertNotification($messageCs, $messageEn) {
        $connection = $this->getConnection();
        $connection->insert("notification", [
            'message' => $messageCs,
            'lang' => 'cs',
        ])->execute();
        $connection->insert("notification", [
            'message' => $messageEn,
            'lang' => 'en',
        ])->execute();
    }
}
