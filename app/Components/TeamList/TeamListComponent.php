<?php

namespace FOL\Components\TeamList;

use FOL\Components\BaseComponent;
use FOL\Model\ORM\Services\ServiceCompetitor;
use FOL\Model\ORM\Services\ServiceTeam;

class TeamListComponent extends BaseComponent {

    protected ServiceTeam $serviceTeam;
    protected ServiceCompetitor $serviceCompetitor;

    public function injectPrimary(ServiceCompetitor $serviceCompetitor, ServiceTeam $serviceTeam): void {
        $this->serviceCompetitor = $serviceCompetitor;
        $this->serviceTeam = $serviceTeam;
    }

    protected function beforeRender(): void {
        $this->template->teams = $this->serviceTeam->getTable();
        $this->template->categories = ServiceTeam::getCategoryNames();
    }

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'teamList.latte');
        parent::render();
    }
}
