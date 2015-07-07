<?php

namespace App\Model;

class ChatModel extends AbstractModel {

	public function find($id) {
		$this->checkEmptiness($id, "id");
		return $this->findAll()->where("[id_chat] = %i", $id)->fetch();
	}

	/**
	 * @return \DibiDataSource
	 */
	public function findAll($lang = NULL) {
		$dataSource = $this->getConnection()->dataSource("SELECT * FROM [view_chat]");
                
                if($lang !== NULL){
                    return $dataSource->where("[lang] = %s", $lang);
                }
                return $dataSource;
	}
        
        /**
	 * @return \DibiDataSource
	 */
	public function findAllRoot($lang = NULL) {
            return $this->findAll($lang)->where("[id_parent] IS NULL");
	}
        
        /**
	 * @return \DibiDataSource
	 */
	public function findDescendants($parent_id, $lang = NULL) {
		return $this->findAll($lang)->where("[id_parent] = %i", $parent_id);
	}

	public function insert($team, $content, $parent_id, $lang) {
		$this->checkEmptiness($team, "team");
		$this->checkEmptiness($content, "content");
		$return = $this->getConnection()->insert("chat", array(
                                "id_parent"     => $parent_id,
				"id_team"	=> $team,
				"content"	=> $content,
                                "lang"          => $lang,
				"inserted"	=> new \DateTime()
				))->execute();
		$this->log($team, "chat_inserted", "The team successfuly contributed to the chat.");
		return $return;
	}
}
