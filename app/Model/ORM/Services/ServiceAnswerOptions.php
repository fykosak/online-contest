<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelAnswerOptions;
use FOL\Model\ORM\Models\ModelTask;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

class ServiceAnswerOptions extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'answer_options', ModelAnswerOptions::class);
    }

    public function getAnswerOptions(ModelTask $task): ?ModelAnswerOptions {
        /** @var ModelAnswerOptions|null $results */
        $results = $this->findByPrimary($task->id_task);
        return $results;
    }
}
