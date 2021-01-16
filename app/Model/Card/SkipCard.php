<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use Dibi\Row;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

class SkipCard extends Card {

    protected TasksService $tasksService;
    protected ScoreService $scoreService;

    /**
     * @param Row $team
     * @param Logger $logger
     * @param mixed ...$args
     * @throws BadRequestException
     * @throws Exception
     * @throws ForbiddenRequestException
     */
    protected function innerHandle(Row $team, Logger $logger, ...$args): void {
        if ($this->isUsed($team)) {
            throw new ForbiddenRequestException();
        }
        if (!isset($args[0]) || is_array($args[0])) {
            throw new BadRequestException();
        }
        foreach ($args[0] as $taskId) {
            $task = $this->tasksService->find($taskId);

            $this->tasksService->skip($team, $task);
            //Environment::getCache()->clean(array(Cache::TAGS => array("problems/$team"))); not used
            // TODO label
            $logger->log(new Message(sprintf(_('Úloha %s přeskočena.'), $taskId), 'success'));
            $this->tasksService->updateSingleCounter($team, $task);
            $this->scoreService->updateAfterSkip($team);
        }
    }
}
