<?php

namespace FOL\Components\TeamList;

use FOL\Components\BaseComponent;
use FOL\Model\ORM\Services\ServiceTeam;

final class TeamListComponent extends BaseComponent {

    private ServiceTeam $serviceTeam;

    public function injectPrimary(ServiceTeam $serviceTeam): void {
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
