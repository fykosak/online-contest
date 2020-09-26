<?php

namespace App\FrontendModule\Presenters;

use Dibi\Connection;
use Dibi\Exception;
use FOL\Model\ORM\TasksService;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use App\Model\Authentication\CronAuthenticator;
use Nette\Http\IResponse;
use Nette\Security\AuthenticationException;
use Tracy\Debugger;

class CronPresenter extends BasePresenter {

    private Cache $cache;

    private CronAuthenticator $authenticator;

    private TasksService $tasksService;

    protected Connection $connection;

    public function injectSecondary(IStorage $storage, CronAuthenticator $authenticator, TasksService $tasksService, Connection $connection): void {
        $this->cache = new Cache($storage);
        $this->authenticator = $authenticator;
        $this->connection = $connection;
        $this->tasksService = $tasksService;
    }

    /**
     * @param false $freezed
     * @return void
     * @throws Exception
     */
    public function renderDatabase($freezed = false): void {
        $this->resetTemporaryTables();
        $this->invalidateCache($freezed);
    }

    /**
     * @return void
     * @throws Exception
     */
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
            $this->connection->query("DROP TABLE IF EXISTS [$result$table]");
            $this->connection->query("CREATE TABLE [$result$table] AS SELECT * FROM [$src$view]");
            echo "$table: " . Debugger::timer() . "<br>";
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function renderCounters(): void {
        $this->tasksService->updateCounter(null, true);
    }

    private function invalidateCache($freezed): void {
        //$cache = Environment::getCache('Nette.Template.Curly');
        if ($freezed) {
            $this->cache->clean([Cache::TAGS => [OrgPresenter::STATS_TAG]]);
            echo "<br>FREEZED<br>";
        } else {
            $this->cache->clean([Cache::ALL => true]);
        }
    }

//    private function isCronAccess() {
//        $keyGet = $this->getHttpRequest()->getQuery("cron-key");
//        $keyConf = $this->context->parameters['cron']['key'];
//        return isset($keyGet) && $keyConf == $keyGet;
//    }
    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws AuthenticationException
     */
    protected function startup(): void {
        parent::startup();
        $key = $this->getHttpRequest()->getQuery("cron-key");
        $this->authenticator->login($key);
//        if (!$this->isCronAccess()) {
        if (!$this->user->isAllowed('cron')) {
            //die("PERMISSION DENIED");
            $this->error("PERMISSION DENIED", IResponse::S403_FORBIDDEN);
        }
    }

}
