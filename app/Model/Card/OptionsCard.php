<?php

namespace FOL\Model\Card;

use FOL\Model\Card\Exceptions\TaskNotAvailableException;
use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceAnswerOptions;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class OptionsCard extends SingleFormCard {

    public ServiceAnswerOptions $serviceAnswerOptions;

    public function injectPrimary(ServiceAnswerOptions $serviceAnswerOptions): void {
        $this->serviceAnswerOptions = $serviceAnswerOptions;
    }

    protected function beforeHandle(Logger $logger, array $values): void {
        $taskId = $values['task'];
        if (!isset($this->getTasks()[$taskId])) {
            throw new TaskNotAvailableException();
        }
    }

    public function getType(): string {
        return ModelCardUsage::TYPE_OPTIONS;
    }

    public function getTitle(): string {
        return _('Multiple choice');
    }

    public function getDescription(): Html {
        return Html::el('span')->addText(_('The team receives 4 possible answers to the question, one of them is correct.'));
    }

    public function decorateFormContainer(Container $container, string $lang): void {
        $items = [];
        foreach ($this->getTasks() as $row) {
            $task = ModelTask::createFromActiveRow($row);
            // TODO has every answer options?
            $items[$task->id_task] = $task->getLabel($lang);
        }
        $container->addSelect('task', _('Task'), $items);
    }
}
