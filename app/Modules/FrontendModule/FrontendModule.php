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
        $router[] = new Route('<lang>/<presenter cron|org>/<action>', [
            'module' => 'Frontend',
            'action' => 'default',
            'lang' => 'en',
        ]);

        $router[] = new Route('<lang>/[<module>/[<presenter>/[<action>/[<id>]]]]', [
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

