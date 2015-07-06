<?php

use App\Model\Interlos;

class TeamListComponent extends BaseListComponent {

	protected function beforeRender() {
		$this->getTemplate()->teams = $this->getSource()->fetchAssoc("category,id_team");
		$ids = $this->getSource()->fetchPairs("id_team", "id_team");
		$this->getTemplate()->competitors = Interlos::competitors()->findAll()
				->where("[id_team] IN %l", $ids)
				->orderBy("id_school", "name")
				->fetchAssoc("id_team,id_competitor");
		$this->getTemplate()->categories = Interlos::teams()->getCategoryNames();
	}

}
