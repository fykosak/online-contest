<?php

namespace FOL\Model\ORM;

use DateTime;
use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Result;
use Dibi\Row;

class ChatService extends AbstractService {
    /**
     * @param $id
     * @return Row|false
     * @throws Exception
     */
    public function find($id) {
        return $this->findAll()->where("[id_chat] = %i", $id)->fetch();
    }

    /**
     * @param null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findAll($lang = null): DataSource {
        $dataSource = $this->getDibiConnection()->dataSource("SELECT * FROM [view_chat]");

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
    public function findAllRoot($lang = null): DataSource {
        return $this->findAll($lang)->where("[id_parent] IS NULL");
    }

    /**
     * @param $parent_id
     * @param null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findDescendants($parent_id, $lang = null): DataSource {
        return $this->findAll($lang)->where("[id_parent] = %i", $parent_id);
    }

    /**
     * @param $team
     * @param $org
     * @param $content
     * @param $parent_id
     * @param $lang
     * @return Result|int
     * @throws Exception
     */
    public function insert($team, $org, $content, $parent_id, $lang) {
        $return = $this->getDibiConnection()->insert("chat", [
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

    protected function getTableName(): string {
        return 'chat';
    }
}
