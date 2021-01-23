<?php

namespace FOL\Modules\FrontendModule;

use FOL\Model\ORM\TasksService;
use FOL\Modules\OrgModule\OrgPresenter;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Caching\Cache;
use FOL\Model\Authentication\CronAuthenticator;
use Nette\Caching\Storage;
use Nette\Database\Explorer;
use Nette\Http\IResponse;
use Nette\Security\AuthenticationException;
use Tracy\Debugger;

final class CronPresenter extends BasePresenter {

    private Cache $cache;

    private CronAuthenticator $authenticator;
    private TasksService $tasksService;
    private Explorer $explorer;

    public function injectSecondary(Storage $storage, CronAuthenticator $authenticator, TasksService $tasksService, Explorer $explorer): void {
        $this->cache = new Cache($storage);
        $this->authenticator = $authenticator;
        $this->tasksService = $tasksService;
        $this->explorer = $explorer;
    }

    public function renderDatabase(bool $freezed = false): void {
        $this->resetTemporaryTables();
        $this->invalidateCache($freezed);
    }

    private function resetTemporaryTables(): void {
        $src = 'view_'; // view
        $result = 'tmp_'; // resulting cache

        $tables = [
            //'task_result' => 'task_result',
            'task_stat' => 'task_stat',
            'penality' => 'penality',
            'bonus' => 'bonus',
            'total_result_cached' => 'total_result',
        ];

        foreach ($tables as $view => $table) {
            Debugger::timer();
            $this->explorer->query("DROP TABLE IF EXISTS [$result$table]");
            $this->explorer->query("CREATE TABLE [$result$table] AS SELECT * FROM [$src$view]");
            echo "$table: " . Debugger::timer() . '<br>';
        }
    }

    public function renderCounters(): void {
        $this->tasksService->updateCounter(true);
    }

    private function invalidateCache(bool $freezed): void {
        //$cache = Environment::getCache('Nette.Template.Curly');
        if ($freezed) {
            $this->cache->clean([Cache::TAGS => [OrgPresenter::STATS_TAG]]);
            echo '<br>FREEZED<br>';
        } else {
            $this->cache->clean([Cache::ALL => true]);
        }
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws AuthenticationException
     */
    protected function startUp(): void {
        parent::startUp();
        $key = $this->getHttpRequest()->getQuery('cron-key');
        $this->authenticator->login($key);
//        if (!$this->isCronAccess()) {
        if (!$this->user->isAllowed('cron')) {
            //die("PERMISSION DENIED");
            $this->error('PERMISSION DENIED', IResponse::S403_FORBIDDEN);
        }
    }

}
