<?php

namespace App\Model;

class NotificationModel extends AbstractModel {

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_notification] = %i", $id)->fetch();
    }

    /**
     * @return \DibiDataSource
     */
    public function findAll($lang = NULL) {
        $dataSource = $this->getConnection()->dataSource("SELECT * FROM [notification]");
        
        if($lang !== NULL){
            return $dataSource->where("[lang] = %s", $lang);
        }
        return $dataSource;
    }
    
    /**
     * @return \DibiDataSource
     */
    public function findActive($lang = NULL) {
        return $this->findAll($lang)->where("[created] < NOW()")->orderBy('created', 'DESC');
    }
    
    /**
     * @return \DibiDataSource
     */
    public function findNew($timestamp, $lang = NULL) {
        return $this->findActive($lang)->where("[created] > %t", $timestamp)->orderBy('created');
    }
    
    public function insert($message, $lang) {
        $this->getConnection()->insert("notification", array(
            'message' => $message,
            'lang' => $lang
        ))->execute();
    }
    
    public function insertNotification($messageCs, $messageEn) {
        $connection = $this->getConnection();
        $connection->insert("notification", array(
            'message' => $messageCs,
            'lang' => 'cs'
        ))->execute();
        $connection->insert("notification", array(
            'message' => $messageEn,
            'lang' => 'en'
        ))->execute();
    }
}