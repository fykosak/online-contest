<?php

namespace FOL\Model\ORM;

use FOL\Model\ORM\Services\ServiceLog;
use Nette\Database\Explorer;
use Nette\SmartObject;

abstract class AbstractService {

    use SmartObject;

    private ServiceLog $serviceLog;
    protected Explorer $explorer;

    public function __construct(Explorer $explorer, ServiceLog $serviceLog) {
        $this->explorer = $explorer;
        $this->serviceLog = $serviceLog;
    }

    public final function log(int $teamId, string $type, string $text): void {
        $this->serviceLog->log($teamId, $type, $text);
    }
}
