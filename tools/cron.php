<?php

namespace FOL\Tools;

use FOL\Model\ORM\TasksService;
use Nette\Database\Explorer;
use Tracy\Debugger;

use FOL\Bootstrap;

define('LIBS_DIR', __DIR__ . '/../libs');
define('APP_DIR', __DIR__ . '/../app');

// Load Nette Framework
require APP_DIR . '/Bootstrap.php';

// Configure application
$configurator = Bootstrap::boot();

$container = $configurator->createContainer();

$tasksService = $container->getByType(TasksService::class);
$explorer = $container->getByType(Explorer::class);

function resetTemporaryTables(Explorer $explorer): void {
    $src = 'view_'; // view
    $result = 'tmp_'; // resulting cache

    $tables = [
        //'task_result' => 'task_result',
        'task_stat' => 'task_stat',
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


