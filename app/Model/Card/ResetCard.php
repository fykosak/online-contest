<?php

namespace FOL\Model\Card;

use Dibi\Row;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\UI\Form;
use Nette\Utils\Html;

class ResetCard extends Card {

    protected function isInnerAvailable(Row $team): bool {
        // TODO: Implement isInnerAvailable() method.
        return true;
    }

    public function decorateForm(Form $form, Row $team, string $lang): void {
        // TODO: Implement decorateForm() method.
    }

    protected function innerHandle(Row $team, Logger $logger, array $values): void {
        // TODO: Implement innerHandle() method.
    }

    public function getType(): string {
        return 'reset';
    }

    public function getTitle(): string {
        return _('Reset points');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }
}
