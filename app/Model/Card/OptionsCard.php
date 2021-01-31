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
        return _('4 options');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
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
