<?php

use App\Model\Interlos;

class TeamListComponent extends BaseListComponent {

	protected function beforeRender() {
		$this->getTemplate()->teams = $this->getSource()->fetchAssoc("category,id_team");
		$ids = $this->getSource()->fetchPairs("id_team", "id_team");
                if(count($ids) > 0) {
                    $this->getTemplate()->competitors = Interlos::competitors()->findAll()
                                    ->where("[id_team] IN %l", $ids)
                                    ->orderBy("id_school", "name")
                                    ->fetchAssoc("id_team,id_competitor");
                }
                else {
                    $this->getTemplate()->competitors = [];
                }
		$this->getTemplate()->categories = Interlos::teams()->getCategoryNames();
	}

}
