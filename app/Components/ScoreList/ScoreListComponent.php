<?php

namespace FOL\Components\ScoreList;

use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FOL\Model\GameSetup;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTaskState;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\Services\ServiceTaskState;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Model\ORM\TasksService;
use Fykosak\Utils\FrontEndComponents\AjaxComponent;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\DI\Container;
use Nette\Utils\DateTime;
use Throwable;

class ScoreListComponent extends AjaxComponent {

    protected TasksService $tasksService;
    protected ServiceTeam $serviceTeam;
    protected ScoreService $scoreService;
    private ServiceTaskState $serviceTaskState;
    private Storage $storage;
    private Cache $cache;
    private GameSetup $gameSetup;
    private ?string $lastUpdated = null;

    public function __construct(Container $container) {
        parent::__construct($container, 'score-list');
        $this->cache = new Cache($this->storage, self::class);
    }

    public function injectPrimary(
        TasksService $tasksService,
        ServiceTeam $serviceTeam,
        ScoreService $scoreService,
        ServiceTaskState $serviceTaskState,
        Storage $storage,
        GameSetup $gameSetup
    ): void {
        $this->tasksService = $tasksService;
        $this->serviceTeam = $serviceTeam;
        $this->scoreService = $scoreService;
        $this->serviceTaskState = $serviceTaskState;
        $this->storage = $storage;
        $this->gameSetup = $gameSetup;
    }

    /**
     * @param string $lastUpdated
     * @return void
     * @throws AbortException
     */
    public function handleRefresh(string $lastUpdated): void {
        $this->lastUpdated = $lastUpdated;
        $this->sendAjaxResponse();
    }

    /**
     * @return array
     * @throws Throwable
     */
    protected function getData(): array {
        $isOrg = true; // TODO
        return array_merge([
            'times' => $this->calculateTimes(),
            'gameStart' => new \DateTime('2021-01-25 00:00:00'),
            'gameEnd' => new \DateTime('2021-02-25 00:00:00'),
            'lastUpdated' => (new DateTime())->format('c'),
            'refreshDelay' => 30, // TODO to config
            'isOrg' => $isOrg,
        ], $this->cache->load('results-3', function (&$dependencies) use ($isOrg): array {
            $dependencies[Cache::EXPIRE] = '1 minute';
            return [
                'availablePoints' => [5, 3, 2, 1],
                'categories' => array_keys(ServiceTeam::getCategoryNames()),
                'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
                'teams' => $this->serialiseTeams(),
                'tasks' => $this->serialiseTasks(),
                'submits' => ($this->isResultsVisible() || $isOrg) ? $this->serialiseSubmits() : [],
            ];
        }));
        /*
         *  $this->template->bonus = $this->scoreService->findAllBonus()->fetchAssoc('id_team');
        $this->template->penality = $this->scoreService->findAllPenality()->fetchAssoc('id_team');
        $this->template->lang = $this->presenter->lang;
        $this->template->categories = $this->serviceTeam->getCategoryNames();
         */
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    protected function getResponseData(): array {
        $this->addAction('refresh', 'refresh!', ['lastUpdated' => (new DateTime())->format('c')]);
        return parent::getResponseData();
    }

    private function calculateTimes(): array {
        return [
            'toEnd' => strtotime($this->gameSetup->gameStart) - time(),
            'toStart' => strtotime($this->gameSetup->gameEnd) - time(),
            'visible' => $this->isResultsVisible(),
        ];
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

    /**
     *  Take care, this function is not state-less!!!
     */
    public function isResultsVisible(): bool {
        if ($this->gameSetup->hardVisible) {
            return true;
        }
        $before = (time() < strtotime($this->gameSetup->resultsHide));
        $after = (time() > strtotime($this->gameSetup->resultsDisplay));
        return ($before && $after);
    }
}
