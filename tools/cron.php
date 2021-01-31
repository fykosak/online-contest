<?php

namespace FOL\Tools;

use FOL\Model\Authentication\CronAuthenticator;
use FOL\Model\ORM\TasksService;
use Nette\Database\Explorer;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Security\User;
use Tracy\Debugger;

use FOL\Bootstrap;

define('LIBS_DIR', __DIR__ . '/../libs');
define('APP_DIR', __DIR__ . '/../app');

// Load Nette Framework
require APP_DIR . '/Bootstrap.php';

// Configure application
$configurator = Bootstrap::boot();

$container = $configurator->createContainer();

$authenticator = $container->getByType(CronAuthenticator::class);
$tasksService = $container->getByType(TasksService::class);
$explorer = $container->getByType(Explorer::class);
$request = $container->getByType(IRequest::class);
$user = $container->getByType(User::class);

$key = $request->getQuery('cron-key');
$authenticator->login($key);
if (!$user->isAllowed('cron')) {
    $this->error('PERMISSION DENIED', IResponse::S403_FORBIDDEN);
}

function resetTemporaryTables(Explorer $explorer): void {
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
        $explorer->query("DROP TABLE IF EXISTS [$result$table]");
        $explorer->query("CREATE TABLE [$result$table] AS SELECT * FROM [$src$view]");
        echo "$table: " . Debugger::timer() . '<br>';
    }
}

function renderCounters(TasksService $tasksService): void {
    $tasksService->updateCounter(true);
}

resetTemporaryTables($explorer);
renderCounters($tasksService);


