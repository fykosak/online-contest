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
     * @return Row|null
     * @throws Exception
     */
    public function find(int $id): ?Row {
        return $this->findAll()->where('[id_chat] = %i', $id)->fetch();
    }

    /**
     * @param string|null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findAll(?string $lang = null): DataSource {
        $dataSource = $this->getDibiConnection()->dataSource('SELECT * FROM [view_chat]');

        if ($lang) {
            return $dataSource->where('[lang] = %s', $lang);
        }
        return $dataSource;
    }

    /**
     * @param string|null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findAllRoot(?string $lang = null): DataSource {
        return $this->findAll($lang)->where('[id_parent] IS NULL');
    }

    /**
     * @param $parent_id
     * @param string|null $lang
     * @return DataSource
     * @throws Exception
     */
    public function findDescendants($parent_id, ?string $lang = null): DataSource {
        return $this->findAll($lang)->where('[id_parent] = %i', $parent_id);
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
    public function insert($team, $org, $content, $parent_id, string $lang) {
        $return = $this->getDibiConnection()->insert('chat', [
            'id_parent' => $parent_id,
            'id_team' => $team,
            'org' => $org,
            'content' => $content,
            'lang' => $lang,
            'inserted' => new DateTime(),
        ])->execute();
        $this->log($team, 'chat_inserted', 'The team successfuly contributed to the chat.');
        return $return;
    }

    protected function getTableName(): string {
        return 'chat';
    }
}
