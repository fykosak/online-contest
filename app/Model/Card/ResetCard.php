<?php

namespace FOL\Model\Card;

use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ScoreStrategy;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class ResetCard extends SingleFormCard {

    private ScoreStrategy $scoreStrategy;

    public function injectScoreStrategy(ScoreStrategy $scoreStrategy): void {
        $this->scoreStrategy = $scoreStrategy;
    }

    public function checkRequirements(): void {
        parent::checkRequirements();
        // TODO: Implement isInnerAvailable() method.
    }

    public function decorateFormContainer(Container $container, string $lang): void {
        $unsolved = $this->tasksService->findUnsolved($this->team);
        $items = [];
        foreach ($unsolved as $taskId) {
            /** @var ModelTask $task */
            $task = $this->serviceTask->findByPrimary($taskId);
            $items[$task->id_task] = $task->getLabel($lang) . ' - ' . $this->scoreStrategy->getSingleTaskScore($this->team, $task).'b.';
        }
        $container->addSelect('task', _('Task'), $items);
    }

    protected function innerHandle(Logger $logger, array $values): void {
        // TODO: Implement innerHandle() method.
    }

    public function getType(): string {
        return ModelCardUsage::TYPE_RESET;
    }

    public function getTitle(): string {
        return _('Reset points');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }
}
