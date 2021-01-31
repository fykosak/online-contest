<?php

namespace FOL\Model\Card;

use FOL\Model\ORM\Models\ModelCardUsage;
use Fykosak\Utils\Logging\Logger;
use Nette\Utils\Html;

final class DoublePointsCard extends Card {

    protected function innerHandle(Logger $logger, array $values): void {
        // intentionally blank!
    }

    public function getType(): string {
        return ModelCardUsage::TYPE_DOUBLE_POINTS;
    }

    public function getTitle(): string {
        return _('Double points');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }
}
