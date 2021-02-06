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
        $router->addRoute('error', [
            'module' => 'Frontend',
            'presenter' => 'Error',
            'action' => 'default',
        ]);

        $router->addRoute('<module public|org>/[<presenter>/[<action>/[<id>]]]', [
            'presenter' => 'Default',
            'action' => 'default',
        ]);

        $router->addRoute('[<presenter>/[<action>/[<id>]]]', [
            'module' => 'Game',
            'presenter' => 'Default',
            'action' => 'default',
        ]);

        return $router;
    }

    public static function getModuleDir(): string {
        return APP_DIR . '/FrontendModule';
    }
}
