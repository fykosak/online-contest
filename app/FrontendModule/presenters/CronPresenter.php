<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos,
    Nette,
    Nette\Caching\Cache,
    Nette\Caching\IStorage;

class CronPresenter extends BasePresenter {
    
    /** @var Nette\Caching\Cache */
    private $cache;
    
    public function __construct(Interlos $interlos, IStorage $storage) {
        parent::__construct($interlos);
        $this->cache = new Cache($storage);
    }

    public function renderDatabase($key) {
        Interlos::resetTemporaryTables();
        $this->invalidateCache();
    }

    public function renderCounters($key) {
        Interlos::tasks()->updateCounter(null, true);
    }

    private function invalidateCache() {
        //$cache = Environment::getCache('Nette.Template.Curly');
        $this->cache->clean(array(Cache::ALL => true));
    }
    
    private function isCronAccess() {
        $keyGet = $this->getHttpRequest()->getQuery("cron-key");
        $keyConf = $this->context->parameters['cron']['key'];
        return isset($keyGet) && $keyConf == $keyGet;
    }

    protected function startup() {
        parent::startup();
        if (!$this->isCronAccess()) {
            //die("PERMISSION DENIED");
            $this->error("PERMISSION DENIED", Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }

}
