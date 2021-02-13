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

    public function decorateFormContainer(Container $container, string $lang): void {
        $items = [];
        foreach ($this->team->getSubmitAvailableTasks()->select('group:task.id_task AS id_task') as $row) {
            /** @var ModelTask $task */
            $task = $this->serviceTask->findByPrimary($row->id_task);
            $items[$task->id_task] = $task->getLabel($lang) . ' - ' . $this->scoreStrategy->getSingleTaskScore($this->team, $task) . _('b.');
        }
        $container->addSelect('task', _('Task'), $items);
    }

    public function getType(): string {
        return ModelCardUsage::TYPE_RESET;
    }

    public function getTitle(): string {
        return _('Reset points');
    }

    public function getDescription(): Html {
        return Html::el('span')->addText(_('The marking scheme of the problem is reset, the team can get 5 points for a correct answer again.'));
    }
}
