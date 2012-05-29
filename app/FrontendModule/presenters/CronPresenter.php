<?php

class Frontend_CronPresenter extends Frontend_BasePresenter {

    public function renderDatabase($key) {
        Interlos::resetTemporaryTables();
    }

    public function renderCache($key) {
        Environment::getCache()->clean("problems");
    }

    protected function startup() {
        parent::startup();
        if ($_GET["cron-key"] != Environment::getConfig("cron")->key) {
            die("PERMISSION DENIED");
        }
    }

}
