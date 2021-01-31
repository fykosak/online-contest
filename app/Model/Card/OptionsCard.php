<?php

namespace FOL\Model\Card;

use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Services\ServiceAnswerOptions;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class OptionsCard extends SingleFormCard {

    public ServiceAnswerOptions $serviceAnswerOptions;

    public function injectPrimary(ServiceAnswerOptions $serviceAnswerOptions): void {
        $this->serviceAnswerOptions = $serviceAnswerOptions;
    }

    protected function innerHandle(Logger $logger, array $values): void {
        // TODO: Implement innerHandle() method.
    }

    public function getType(): string {
        return ModelCardUsage::TYPE_OPTIONS;
    }

    public function getTitle(): string {
        return _('Multiple choice');
    }

    public function getDescription(): Html {
        return Html::el('span')->addText('the team receives 4 possible answers to the question, one of them is correct.');
    }

    public function decorateFormContainer(Container $container, string $lang): void {
        $items = [];
        foreach ($this->getTasks() as $task) {
            // TODO has every answer options?
            $items[$task->id_task] = $task['name_' . $lang];
        }
        $container->addSelect('task', _('Task'), $items);
    }

    public function checkRequirements(): void {
        parent::checkRequirements();
        // TODO: Implement isInnerAvailable() method.
    }
}
