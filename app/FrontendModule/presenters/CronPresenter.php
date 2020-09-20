<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use App\Model\Authentication\CronAuthenticator;
use Nette\Http\IResponse;

class CronPresenter extends BasePresenter {

    private Cache $cache;

    private CronAuthenticator $authenticator;

    public function __construct(Interlos $interlos, IStorage $storage, CronAuthenticator $authenticator) {
        parent::__construct($interlos);
        $this->cache = new Cache($storage);
        $this->authenticator = $authenticator;
    }

    public function renderDatabase($freezed = false): void {
        Interlos::resetTemporaryTables();
        $this->invalidateCache($freezed);
    }

    public function renderCounters(): void {
        Interlos::tasks()->updateCounter(null, true);
    }

    private function invalidateCache($freezed): void {
        //$cache = Environment::getCache('Nette.Template.Curly');
        if ($freezed) {
            $this->cache->clean([Cache::TAGS => [OrgPresenter::STATS_TAG]]);
            echo "<br>FREEZED<br>";
        } else {
            $this->cache->clean([Cache::ALL => true]);
        }
    }

//    private function isCronAccess() {
//        $keyGet = $this->getHttpRequest()->getQuery("cron-key");
//        $keyConf = $this->context->parameters['cron']['key'];
//        return isset($keyGet) && $keyConf == $keyGet;
//    }

    protected function startup(): void {
        parent::startup();
        $key = $this->getHttpRequest()->getQuery("cron-key");
        $this->authenticator->login($key);
//        if (!$this->isCronAccess()) {
        if (!$this->user->isAllowed('cron')) {
            //die("PERMISSION DENIED");
            $this->error("PERMISSION DENIED", IResponse::S403_FORBIDDEN);
        }
    }

}
