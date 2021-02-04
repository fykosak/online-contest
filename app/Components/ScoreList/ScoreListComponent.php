<?php

namespace FOL\Components\ScoreList;

use FOL\Model\GameSetup;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTaskState;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceTask;
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

final class ScoreListComponent extends AjaxComponent {

    private TasksService $tasksService;
    private ServiceTeam $serviceTeam;
    private ServiceTaskState $serviceTaskState;
    private Storage $storage;
    private Cache $cache;
    private GameSetup $gameSetup;
    private ServiceTask $serviceTask;

    public function __construct(Container $container) {
        parent::__construct($container, 'score-list');
        $this->cache = new Cache($this->storage, self::class);
    }

    public function injectPrimary(
        TasksService $tasksService,
        ServiceTeam $serviceTeam,
        ServiceTaskState $serviceTaskState,
        ServiceTask $serviceTask,
        Storage $storage,
        GameSetup $gameSetup
    ): void {
        $this->tasksService = $tasksService;
        $this->serviceTeam = $serviceTeam;
        $this->serviceTaskState = $serviceTaskState;
        $this->storage = $storage;
        $this->gameSetup = $gameSetup;
        $this->serviceTask = $serviceTask;
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleRefresh(): void {
        $this->sendAjaxResponse();
    }

    /**
     * @return array
     * @throws Throwable
     */
    protected function getData(): array {
        $isOrg = true; // TODO
        $data = array_merge([
            'times' => $this->calculateTimes(),
            'lastUpdated' => (new DateTime())->format('c'),
            'refreshDelay' => $this->gameSetup->refreshDelay,
            'isOrg' => $isOrg,

        ], $this->cache->load('resultsa2', function (&$dependencies) use ($isOrg): array {
            $dependencies[Cache::EXPIRE] = '30 second';
            return [
                'gameStart' => new \DateTime('2021-01-25 00:00:00'),
                'gameEnd' => new \DateTime('2021-02-25 00:00:00'),
                'availablePoints' => [5, 3, 2, 1],
                'categories' => array_keys(ServiceTeam::getCategoryNames()),
                'basePath' => $this->getHttpRequest()->getUrl()->getBasePath(),
                'teams' => $this->serialiseTeams(),
                'tasks' => $this->serialiseTasks(),
                'submits' => $this->serialiseSubmits(),
            ];
        }));
        if (!$this->gameSetup->isResultsVisible() && !$isOrg) {
            $data['submits'] = []; // unset submits
        }
        return $data;
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    protected function getResponseData(): array {
        $this->addAction('refresh', 'refresh!');
        return parent::getResponseData();
    }

    private function calculateTimes(): array {
        return [
            'toEnd' => strtotime($this->gameSetup->gameStart) - time(),
            'toStart' => strtotime($this->gameSetup->gameEnd) - time(),
            'visible' => $this->gameSetup->isResultsVisible(),
        ];
    }

    private function serialiseTeams(): array {
        $teams = [];
        /** @var ModelTeam $row */
        foreach ($this->serviceTeam->getTable() as $row) {
            $teams[] = $row->__toArray();
        }
        return $teams;
    }

    private function serialiseTasks(): array {
        $tasks = [];
        /** @var ModelTask $task */
        foreach ($this->template->tasks = $this->serviceTask->getTable()->order('id_group')->order('number') as $task) {
            $tasks[] = $task->__toArray();
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
