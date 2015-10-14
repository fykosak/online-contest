<?php

namespace App\FrontendModule;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;

class FrontendModule extends Nette\Object {

    /**
    *  @return \Nette\Application\IRouter
    */
    public static function createRouter() {
        $router = new RouteList();

        $router[] = new Route('index.php', array(
                    "module" => "Frontend",
                    "presenter" => "Default",
                    "action" => "default",
                        ), Route::ONE_WAY);
        $router[] = new Route("<lang>/<presenter>/<action>", array(
                    "module" => "Frontend",
                    "presenter" => "Default",
                    "action" => "default",
                    "lang" => null,
                ));

        return $router;
    }
    
    
//	public static function createRouter()
//	{
//		$router = new RouteList();
//		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
//		return $router;
//	}

    public static function getModuleDir() {
        return APP_DIR . "/FrontendModule";
    }

}

