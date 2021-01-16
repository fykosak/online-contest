<?php

namespace FOL\Model\Router;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class Router {

    public static function createRouter(): \Nette\Routing\Router {
        $router = new RouteList();

        $router->addRoute('index.php', [
            'module' => 'Frontend',
            'presenter' => 'Default',
            'action' => 'default',
        ], Route::ONE_WAY);
        $router->addRoute('<lang>/<presenter cron>/<action>', [
            'module' => 'Frontend',
            'action' => 'default',
            'lang' => 'en',
        ]);

        $router->addRoute('<lang>/[<module>/[<presenter>/[<action>/[<id>]]]]', [
            'module' => 'Public',
            'presenter' => 'Default',
            'action' => 'default',
            'lang' => 'en',
        ]);

        return $router;
    }

    public static function getModuleDir(): string {
        return APP_DIR . '/FrontendModule';
    }
}
