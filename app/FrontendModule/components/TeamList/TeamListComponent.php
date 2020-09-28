<?php

use FOL\Model\ORM\CompetitorsService;
use FOL\Model\ORM\TeamsService;

class TeamListComponent extends BaseListComponent {

    protected TeamsService $teamsService;
    protected CompetitorsService $competitorsService;

    public function injectPrimary(CompetitorsService $competitorsService, TeamsService $teamsService): void {
        $this->teamsService = $teamsService;
        $this->competitorsService = $competitorsService;
    }

    /**
     * @return void
     * @throws \Dibi\Exception
     */
    protected function beforeRender(): void {
        $this->getTemplate()->teams = $this->getSource()->fetchAssoc("category,id_team");
        $ids = $this->getSource()->fetchPairs("id_team", "id_team");
        if (count($ids) > 0) {
            $this->getTemplate()->competitors = $this->competitorsService->findAll()
                ->where("[id_team] IN %l", $ids)
                ->orderBy("id_school", "name")
                ->fetchAssoc("id_team,id_competitor");
        } else {
            $this->getTemplate()->competitors = [];
        }
        $this->getTemplate()->categories = $this->teamsService->getCategoryNames();
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'teamList.latte');
        parent::render();
    }
}
