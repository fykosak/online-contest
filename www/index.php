<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require __DIR__ . '/.maintenance.php';

use FOL\Bootstrap;
use Nette\Application\Application;

require __DIR__ . '/../app/Bootstrap.php';

// inicializace prostředí + získání objektu Nette\Configurator
$configurator = Bootstrap::boot();
// vytvoření DI kontejneru
$container = $configurator->createContainer();
// DI kontejner vytvoří objekt Nette\Application\Application
$application = $container->getByType(Application::class);
// spuštění Nette aplikace
$application->run();
