<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelAnswerOptions;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceAnswerOptions extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'answer_options', ModelAnswerOptions::class);
    }

    public function getAnswerOptions(int $taskId): ?ModelAnswerOptions {
        /** @var ModelAnswerOptions|null $results */
        $results = $this->findByPrimary($taskId);
        return $results;
    }
}
