<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos,
    Nette,
    Nette\Caching\Cache,
    Nette\Caching\IStorage,
    App\Model\Authentication\CronAuthenticator;

class CronPresenter extends BasePresenter {
    
    /** @var Nette\Caching\Cache */
    private $cache;
    
    /** @var App\Model\Authentication\CronAuthenticator */
    private $authenticator;
    
    public function __construct(Interlos $interlos, IStorage $storage, CronAuthenticator $authenticator) {
        parent::__construct($interlos);
        $this->cache = new Cache($storage);
        $this->authenticator = $authenticator;
    }

    public function renderDatabase($freezed=false) {
        Interlos::resetTemporaryTables();
        $this->invalidateCache($freezed);
    }

    public function renderCounters() {
        Interlos::tasks()->updateCounter(null, true);
    }

    private function invalidateCache($freezed) {
        //$cache = Environment::getCache('Nette.Template.Curly');
        if($freezed){
            $this->cache->clean(array(Cache::TAGS => [OrgPresenter::STATS_TAG]));
            echo "<br>FREEZED<br>";
        }
        else{
            $this->cache->clean(array(Cache::ALL => true));
        }
    }
    
//    private function isCronAccess() {
//        $keyGet = $this->getHttpRequest()->getQuery("cron-key");
//        $keyConf = $this->context->parameters['cron']['key'];
//        return isset($keyGet) && $keyConf == $keyGet;
//    }

    protected function startup() {
        parent::startup();
        $key = $this->getHttpRequest()->getQuery("cron-key");
        $this->authenticator->login($key);
//        if (!$this->isCronAccess()) {
        if (!$this->user->isAllowed('cron')){
            //die("PERMISSION DENIED");
            $this->error("PERMISSION DENIED", Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }

}
