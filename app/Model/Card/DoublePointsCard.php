<?php

namespace FOL\Model\Card;

use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Container;
use Nette\Utils\Html;

class DoublePointsCard extends Card {

    public function checkRequirements(?array $values = null): void {
        // TODO: Implement isInnerAvailable() method.
    }

    public function decorateFormContainer(Container $container, string $lang): void {
        // TODO: Implement decorateForm() method.
    }

    protected function innerHandle(Logger $logger, array $values): void {
        // intentionally blank!
    }

    public function getType(): string {
        return 'double_points';
    }

    public function getTitle(): string {
        return _('Double points');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }
}
