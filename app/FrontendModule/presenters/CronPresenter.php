<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos;

class CronPresenter extends BasePresenter {

    public function renderDatabase($key) {
        Interlos::resetTemporaryTables();
        $this->invalidateCache();
    }

    public function renderCounters($key) {
        Interlos::tasks()->updateCounter(null, true);
    }

    private function invalidateCache() {
        $cache = Environment::getCache('Nette.Template.Curly');
        $cache->clean(array(Cache::ALL => true)); // clean w/out tags, it's broken in Nette 0.9        
    }

    protected function startup() {
        parent::startup();
        if ($_GET["cron-key"] != Environment::getConfig("cron")->key) {
            die("PERMISSION DENIED");
        }
    }

}
