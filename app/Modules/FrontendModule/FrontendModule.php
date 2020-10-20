<?php

namespace FOL\Modules\FrontendModule;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class FrontendModule {

    public static function createRouter(): IRouter {
        $router = new RouteList();

        $router[] = new Route('index.php', [
            'module' => 'Frontend',
            'presenter' => 'Default',
            'action' => 'default',
        ], Route::ONE_WAY);
        $router[] = new Route('<lang>/frontend-module/<presenter>/<action>', [
            'module' => 'Frontend',
            'action' => 'default',
            'lang' => null,
        ]);
        $router[] = new Route('[<lang>[/public-module/[<presenter>/[<action>/[<id>]]]]]', [
            'module' => 'Public',
            'presenter' => 'Default',
            'action' => 'default',
            'lang' => 'en',
        ]);
        $router[] = new Route('<lang>/game-module/[<presenter>[/<action>[/<id>]]]', [
            'module' => 'Game',
            'presenter' => 'Game',
            'action' => 'default',
            'lang' => null,
        ]);


        return $router;
    }

    public static function getModuleDir(): string {
        return APP_DIR . '/FrontendModule';
    }
}

