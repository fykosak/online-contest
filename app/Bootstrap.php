<?php

namespace FOL;

use Nette\Configurator;

require __DIR__ . '/../vendor/autoload.php';

class Bootstrap {

    public static function boot(): Configurator {
        $configurator = new Configurator();

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
        $configurator->enableDebugger(__DIR__ . '/../log');
        //error_reporting(~E_USER_DEPRECATED);

        $configurator->setTempDirectory(__DIR__ . '/../temp');

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->addDirectory(__DIR__ . '/../vendor/others')
            ->register();

        $configurator->addConfig(__DIR__ . '/config/config.neon');
        $configurator->addConfig(__DIR__ . '/config/config.local.neon');
        return $configurator;
    }
}
