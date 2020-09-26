<?php

namespace App\FrontendModule;

use Nette\Application\IRouter;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class FrontendModule {

    public static function createRouter(): IRouter {
        $router = new RouteList();

        $router[] = new Route('index.php', [
            'module' => 'Frontend',
            'presenter' => 'Default',
            'action' => 'default',
        ], Route::ONE_WAY);
        $router[] = new Route('<lang>/<presenter>/<action>', [
            'module' => 'Frontend',
            'presenter' => 'Default',
            'action' => 'default',
            'lang' => null,
        ]);

        $router[] = new Route('<lang>/game/<presenter>/<action>/<id>', [
            'module' => 'Game',
            'action' => 'default',
            'lang' => null,
        ]);

        return $router;
    }

    public static function getModuleDir(): string {
        return APP_DIR . '/FrontendModule';
    }
}

