<?php

namespace FOL\Components\TeamList;

use Dibi\Exception;
use FOL\Model\ORM\CompetitorsService;
use FOL\Model\ORM\TeamsService;
use FOL\Components\BaseListComponent;

class TeamListComponent extends BaseListComponent {

    protected TeamsService $teamsService;
    protected CompetitorsService $competitorsService;

    public function injectPrimary(CompetitorsService $competitorsService, TeamsService $teamsService): void {
        $this->teamsService = $teamsService;
        $this->competitorsService = $competitorsService;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function beforeRender(): void {
        $this->template->teams = $this->getSource()->fetchAssoc('category,id_team');
        $ids = $this->getSource()->fetchPairs('id_team', 'id_team');
        if (count($ids) > 0) {
            $this->template->competitors = $this->competitorsService->findAll()
                ->where('[id_team] IN %l', $ids)
                ->orderBy('id_school', 'name')
                ->fetchAssoc('id_team,id_competitor');
        } else {
            $this->template->competitors = [];
        }
        $this->template->categories = $this->teamsService->getCategoryNames();
    }

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'teamList.latte');
        parent::render();
    }
}
