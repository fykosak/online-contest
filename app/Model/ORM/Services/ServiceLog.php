<?php

namespace FOL\Model\ORM\Services;

use DateTime;
use FOL\Model\ORM\Models\ModelLog;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServiceLog extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'log', ModelLog::class);
    }

    public final function log(?int $teamId, string $type, string $text): ModelLog {
        /** @var ModelLog $log */
        $log = $this->createNewModel([
            'id_team' => $teamId,
            'type' => $type,
            'text' => $text,
            'inserted' => new DateTime(),
        ]);
        return $log;
    }
}
