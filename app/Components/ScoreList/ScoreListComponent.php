<?php

namespace FOL\Components\ScoreList;

use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTaskState;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\Services\ServiceTaskState;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Model\ORM\TasksService;
use Fykosak\Utils\FrontEndComponents\FrontEndComponent;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\DI\Container;
use Nette\Utils\DateTime;
use Throwable;

class ScoreListComponent extends FrontEndComponent {

    protected TasksService $tasksService;
    protected ServiceTeam $serviceTeam;
    protected ScoreService $scoreService;
    private ServiceTaskState $serviceTaskState;
    private Storage $storage;
    private Cache $cache;

    public function __construct(Container $container) {
        parent::__construct($container, 'score-list');
        $this->cache = new Cache($this->storage, self::class);
    }

    public function injectPrimary(
        TasksService $tasksService,
        ServiceTeam $serviceTeam,
        ScoreService $scoreService,
        ServiceTaskState $serviceTaskState,
        Storage $storage
    ): void {
        $this->tasksService = $tasksService;
        $this->serviceTeam = $serviceTeam;
        $this->scoreService = $scoreService;
        $this->serviceTaskState = $serviceTaskState;
        $this->storage = $storage;
    }

    /**
     * @return array
     * @throws Throwable
     */
    protected function getData(): array {
        return array_merge([
            'times' => [
                'toEnd' => 160 * 1000,
                'toStart' => -160 * 1000,
                'visible' => true,
            ],
            'gameStart' => new \DateTime('2021-01-25 00:00:00'),
            'gameEnd' => new \DateTime('2021-02-25 00:00:00'),
            'lastUpdated' => (new DateTime())->format('c'),
            'refreshDelay' => 30 * 1000,
            'isOrg' => true,
        ], $this->cache->load('results5', function (&$dependencies): array {
            // $dependencies[Cache::EXPIRE] = '1 seconds';
            return [
                'availablePoints' => [5, 3, 2, 1],
                'categories' => array_keys(ServiceTeam::getCategoryNames()),
                'basePath' => '/',
                'teams' => $this->serialiseTeams(),
                'tasks' => $this->serialiseTasks(),
                'submits' => $this->serialiseSubmits(),
                'tasksOnBoard' => 7,
            ];
        }));
        /*
         *  $this->template->bonus = $this->scoreService->findAllBonus()->fetchAssoc('id_team');
        $this->template->penality = $this->scoreService->findAllPenality()->fetchAssoc('id_team');
        $this->template->lang = $this->presenter->lang;
        $this->template->categories = $this->serviceTeam->getCategoryNames();
         */
    }

    private function serialiseTeams(): array {
        $teams = [];
        foreach ($this->serviceTeam->findAllWithScore() as $row) {
            $teams[] = ModelTeam::__toArray($row);
        }
        return $teams;
    }

    private function serialiseTasks(): array {
        $tasks = [];
        foreach ($this->template->tasks = $this->tasksService->findPossiblyAvailable() as $row) {
            $tasks[] = ModelTask::__toArray($row);
        }
        return $tasks;
    }

    private function serialiseSubmits(): array {
        $submits = [];
        /** @var ModelTaskState $submit */
        foreach ($this->serviceTaskState->getTable() as $submit) {
            $submits[] = $submit->__toArray();
        }
        return $submits;
    }
}
