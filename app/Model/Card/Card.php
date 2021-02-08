<?php

namespace FOL\Model\Card;

use FOL\Model\Card\Exceptions\CardAlreadyUsedException;
use FOL\Model\Card\Exceptions\CardCannotBeUsedException;
use FOL\Model\GameSetup;
use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceCardUsage;
use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ORM\TasksService;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Explorer;
use Nette\SmartObject;
use Nette\Utils\Html;
use Throwable;

abstract class Card {

    use SmartObject;

    protected Explorer $explorer;
    protected ServiceCardUsage $serviceCardUsage;
    protected ModelTeam $team;
    protected TasksService $tasksService;
    protected ServiceTask $serviceTask;
    protected GameSetup $gameSetup;
    /* cache*/
    private array $tasks;

    public function __construct(ModelTeam $team) {
        $this->team = $team;
    }

    public function injectBase(Explorer $explorer, ServiceCardUsage $serviceCardUsage, TasksService $tasksService, ServiceTask $serviceTask, GameSetup $gameSetup): void {
        $this->explorer = $explorer;
        $this->serviceCardUsage = $serviceCardUsage;
        $this->tasksService = $tasksService;
        $this->serviceTask = $serviceTask;
        $this->gameSetup = $gameSetup;
    }

    public final function wasUsed(): bool {
        return (bool)$this->getUsage();
    }

    public final function getUsage(): ?ModelCardUsage {
        $row = $this->team->related('card_usage')->where('card_type', $this->getType())->fetch();
        return $row ? ModelCardUsage::createFromActiveRow($row) : null;
    }

    public final function logUsage(array $values): ModelCardUsage {
        return $this->serviceCardUsage->createNewModel([
            'team_id' => $this->team->id_team,
            'card_type' => $this->getType(),
            'data' => ModelCardUsage::serializeData($this->getType(), $values),
        ]);
    }

    /**
     * @param Logger $logger
     * @param array $values
     * @throws Throwable
     */
    final public function handle(Logger $logger, array $values): void {
        $this->explorer->beginTransaction();
        try {
            $this->checkRequirements();
            $this->beforeHandle($logger, $values);
            $usage = $this->logUsage($values);
            $this->afterHandle($usage, $logger, $values);
            $this->explorer->commit();
        } catch (Throwable $exception) {
            $this->explorer->rollBack();
            throw $exception;
        }
    }

    protected function getTasks(): array {
        if (!isset($this->tasks)) {
            $this->tasks = [];
            foreach ($this->team->getSubmitAvailableTasks()->select('group:task.id_task AS id_task') as $task) {
                $this->tasks[$task->id_task] = $this->serviceTask->findByPrimary($task->id_task);
            }
        }
        return $this->tasks;
    }

    /**
     * @throws CardCannotBeUsedException
     * @throws ForbiddenRequestException
     */
    public function checkRequirements(): void {
        if ($this->wasUsed()) {
            throw new CardAlreadyUsedException();
        }
        if (!$this->gameSetup->isGameActive()) {
            throw new ForbiddenRequestException();
        }
    }

    protected function beforeHandle(Logger $logger, array $values): void {
    }

    protected function afterHandle(ModelCardUsage $usage, Logger $logger, array $values): void {
    }

    abstract public function getType(): string;

    abstract public function getTitle(): string;

    abstract public function getDescription(): Html;
}
