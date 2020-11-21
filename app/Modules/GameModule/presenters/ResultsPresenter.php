<?php

namespace FOL\Modules\GameModule\Presenters;

use Exception;
use FOL\Model\ORM\TeamsService;
use ScoreListComponent;

class ResultsPresenter extends BasePresenter {

    const STATS_TAG = 'ctStats';

    protected TeamsService $teamsService;

    public function injectTeamsService(TeamsService $teamsService): void {
        $this->teamsService = $teamsService;
    }

    protected function beforeRender(): void {
        parent::beforeRender();
        $this->getTemplate()->categories = $this->teamsService->getCategoryNames();
    }

    public function renderDetail(): void {
        $this->setPageTitle(_('Podrobné výsledky'));
        $this->check('scoreList');
    }

    protected function createComponentScoreList(): ScoreListComponent {
        return new ScoreListComponent($this->getContext());
    }

    private function check($componentName): void {
        try {
            $this->getComponent($componentName);
            $this->getTemplate()->available = true;
        } catch (Exception $e) {
            $this->flashMessage(_('Statistiky jsou momentálně nedostupné. Pravděpodobně dochází k přepočítávání.'), 'danger');
            $this->getTemplate()->available = false;
        }
    }
}
