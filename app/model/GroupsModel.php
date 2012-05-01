<?php
class GroupsModel extends AbstractModel {
	public function find($id) {
		$this->checkEmptiness($id, "id");
		return $this->findAll()->where("[id_group] = %i", $id)->fetch();
	}

	/**
	 * @return DibiDataSource
	 */
	public function findAll() {
		return $this->getConnection()->dataSource("SELECT * FROM [view_group]");
	}

	/**
	 * @return DibiDataSource
	 */
	public function findAllAvailable() {
		return $this->getConnection()->dataSource("SELECT * FROM [view_group] WHERE [to_show] < NOW() ORDER BY [id_group]");
	}
}