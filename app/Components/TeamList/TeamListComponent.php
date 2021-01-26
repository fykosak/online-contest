<?php

namespace FOL\Components\TeamList;

use FOL\Model\ORM\Services\ServiceCompetitor;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Components\BaseListComponent;

class TeamListComponent extends BaseListComponent {

    protected ServiceCompetitor $serviceCompetitor;

    public function injectPrimary(ServiceCompetitor $serviceCompetitor): void {
        $this->serviceCompetitor = $serviceCompetitor;
    }

    protected function beforeRender(): void {
        $this->template->teams = $this->getSource()->fetchAssoc('category|id_team');
        $ids = $this->getSource()->fetchPairs('id_team', 'id_team');
        if (count($ids) > 0) {
            $this->template->competitors = $this->serviceCompetitor->getTable()
                ->where('id_team', $ids)
                ->order('id_school')
                ->order('name')
                ->fetchAssoc('id_team|id_competitor');
        } else {
            $this->template->competitors = [];
        }
        $this->template->categories = ServiceTeam::getCategoryNames();
    }

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'teamList.latte');
        parent::render();
    }
}
