<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use FOL\Model\Card\Exceptions\CardCannotBeUsedException;
use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceCardUsage;
use FOL\Model\ORM\TasksService;
use Fykosak\Utils\Logging\Logger;
use Nette\Database\Explorer;
use Nette\Forms\Container;
use Nette\SmartObject;
use Nette\Utils\Html;
use Throwable;

abstract class Card {

    use SmartObject;

    protected Explorer $explorer;
    protected ServiceCardUsage $serviceCardUsage;
    protected ModelTeam $team;
    protected TasksService $tasksService;
    /* cache*/
    private array $tasks;

    public function __construct(ModelTeam $team) {
        $this->team = $team;
    }

    public function injectBase(Explorer $explorer, ServiceCardUsage $serviceCardUsage, TasksService $tasksService): void {
        $this->explorer = $explorer;
        $this->serviceCardUsage = $serviceCardUsage;
        $this->tasksService = $tasksService;
    }

    public final function wasUsed(): bool {
        return (bool)$this->getUsage();
    }

    public final function getUsage(): ?ModelCardUsage {
        return $this->serviceCardUsage->findByTypeAndTeam($this->team, $this->getType());
    }

    public final function logUsage(array $values): void {
        $this->serviceCardUsage->createNewModel([
            'team_id' => $this->team->id_team,
            'card_type' => $this->getType(),
            'data' => $this->serializeData($values),
        ]);
    }

    protected function serializeData(array $values): string {
        return serialize($values);
    }

    protected function deserializeData(string $values): array {
        return unserialize($values);
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
            $this->innerHandle($logger, $values);
            $this->logUsage($values);
            $this->explorer->commit();
        } catch (Throwable $exception) {
            $this->explorer->rollBack();
            throw $exception;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getTasks(): array {
        if (!isset($this->tasks)) {
            $this->tasks = [];
            foreach ($this->tasksService->findSubmitAvailable($this->team)->fetchAll() as $task) {
                $this->tasks[$task->id_task] = $task;
            }
        }
        return $this->tasks;
    }

    /**
     * @throws CardCannotBeUsedException
     */
    abstract public function checkRequirements(): void;

    abstract public function decorateFormContainer(Container $container, string $lang): void;

    /**
     * @param Logger $logger
     * @param array $values
     * @throws CardCannotBeUsedException
     */
    abstract protected function innerHandle(Logger $logger, array $values): void;

    abstract public function getType(): string;

    abstract public function getTitle(): string;

    abstract public function getDescription(): Html;

    public final function renderUsage(): Html {
        $usage = $this->getUsage();
        $data = $this->deserializeData($usage->data);
        return Html::el('span')->addText('TODO');
    }
}
