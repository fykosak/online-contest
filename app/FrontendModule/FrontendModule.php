<?php

class FrontendModule {

    public static function createRouter() {
        $router = new MultiRouter();

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

    public static function getModuleDir() {
        return APP_DIR . "/FrontendModule";
    }

}

