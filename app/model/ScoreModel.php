<?php

namespace App\Model;

use Nette;

class ScoreModel extends AbstractModel {
	public function find($id) {
		throw new Nette\NotSupportedException();
	}


	public function findAll() {
		throw new Nette\NotSupportedException();
	}

	/** @return \DibiDataSource */
	public function findAllBonus() {
		return $this->getConnection()->dataSource("SELECT * FROM [tmp_bonus]");
	}

	/** @return \DibiDataSource */
	public function findAllTasks() {
		return $this->getConnection()->dataSource("SELECT * FROM [tmp_task_result]");
	}

	/** @return \DibiDataSource */
	public function findAllPenality() {
		return $this->getConnection()->dataSource("SELECT * FROM [tmp_penality]");
	}
        
        /** @return \DibiDataSource */
	public function findAllSkips() {
		return $this->getConnection()->dataSource("SELECT * FROM [task_state] WHERE skipped = 1");
	}
}