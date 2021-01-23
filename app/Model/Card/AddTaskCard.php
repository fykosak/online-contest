<?php

namespace FOL\Model\Card;

use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Container;
use Nette\Utils\Html;

class AddTaskCard extends Card {

    public function checkRequirements(?array $values = null): void {
        parent::checkRequirements();
        // TODO: Implement isInnerAvailable() method.
    }

    public function decorateFormContainer(Container $container, string $lang): void {
        // TODO: Implement decorateForm() method.
    }

    protected function innerHandle(Logger $logger, array $values): void {
        // TODO: Implement innerHandle() method.
    }

    public function getType(): string {
        return 'add_task';
    }

    public function getTitle(): string {
        return _('Add task');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }
}
