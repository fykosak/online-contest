<?php

namespace FOL\Tools;

use FOL\Model\ORM\TasksService;

use FOL\Bootstrap;

define('LIBS_DIR', __DIR__ . '/../libs');
define('APP_DIR', __DIR__ . '/../app');

// Load Nette Framework
require APP_DIR . '/Bootstrap.php';

// Configure application
$configurator = Bootstrap::boot();

$container = $configurator->createContainer();

$tasksService = $container->getByType(TasksService::class);

function renderCounters(TasksService $tasksService): void {
    $tasksService->updateCounter(true);
}

renderCounters($tasksService);
