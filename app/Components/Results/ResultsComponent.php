<?php

namespace FOL\Components\Results;

use FOL\Model\GameSetup;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Components\BaseComponent;
use Nette\Caching\Cache;
use Nette\Caching\Storage;

class ResultsComponent extends BaseComponent {

    protected ScoreService $scoreService;
    private GameSetup $gameSetup;
    private ServiceTeam $serviceTeam;
    private ServiceTask $serviceTask;
    private Cache $cache;

    public function injectPrimary(
        ScoreService $scoreService,
        GameSetup $gameSetup,
        ServiceTeam $serviceTeam,
        ServiceTask $serviceTask,
        Storage $storage
    ): void {
        $this->scoreService = $scoreService;
        $this->serviceTeam = $serviceTeam;
        $this->gameSetup = $gameSetup;
        $this->serviceTask = $serviceTask;
        $this->cache = new Cache($storage, static::class);
    }

    public function render(): void {
        $isOrg = true; // TODO
        $this->template->visible = $isOrg || $this->gameSetup->isResultsVisible();
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'results.latte');
        parent::render();
    }

    protected function beforeRender(): void {
        $data = $this->cache->load('data', function (&$dep) {
            $dep[Cache::EXPIRATION] = '+30 second';
            return [
                'teams' => $this->serviceTeam->getTable(),
                'teamsScore' => $this->serviceTeam->findAllWithScore()->fetchAssoc('id_team'),
                'bonus' => $this->scoreService->findAllBonus()->fetchAssoc('id_team'),
                'penality' => $this->scoreService->findAllPenality(),
            ];
        });
        $this->template->categories = ServiceTeam::getCategoryNames();

        foreach ($data as $key => $datum) {
            $this->template->$key = $datum;
        }

        $maxBonus = 0;
        $maxPoints = 0;
        /** @var ModelTask $task */
        foreach ($this->serviceTask->getTable() as $task) {
            $hurry = ($task->id_group == 1) ? false : true; //dle SQL id_group=2,3,4
            $maxPoints += $task->points;
            if ($hurry) {
                $maxBonus += $task->points;
            }
        }
        $maxPoints += $maxBonus;
        $this->template->maxPoints = $maxPoints;
        $this->template->maxBonus = $maxBonus;
    }
}

