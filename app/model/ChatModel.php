<?php

namespace App\Model;

use DateTime;
use Dibi\DataSource;

class ChatModel extends AbstractModel {

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_chat] = %i", $id)->fetch();
    }


    public function findAll($lang = null): DataSource {
        $dataSource = $this->getConnection()->dataSource("SELECT * FROM [view_chat]");

        if ($lang !== null) {
            return $dataSource->where("[lang] = %s", $lang);
        }
        return $dataSource;
    }

    public function findAllRoot($lang = null): DataSource {
        return $this->findAll($lang)->where("[id_parent] IS NULL");
    }

    public function findDescendants($parent_id, $lang = null): DataSource {
        return $this->findAll($lang)->where("[id_parent] = %i", $parent_id);
    }

    public function insert($team, $org, $content, $parent_id, $lang) {
        $this->checkEmptiness($team, "team");
        $this->checkEmptiness($content, "content");
        $return = $this->getConnection()->insert("chat", [
            "id_parent" => $parent_id,
            "id_team" => $team,
            "org" => $org,
            "content" => $content,
            "lang" => $lang,
            "inserted" => new DateTime(),
        ])->execute();
        $this->log($team, "chat_inserted", "The team successfuly contributed to the chat.");
        return $return;
    }
}
