<?php

namespace FOL\Model\Card;

use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Container;
use Nette\NotImplementedException;
use Nette\Utils\Html;

class ResetCard extends Card {

    public function checkRequirements(): void {
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
        return 'reset';
    }

    public function getTitle(): string {
        return _('Reset points');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }

    protected function innerRenderUsage(string $lang, Html $mainContainer): void {
        throw new NotImplementedException();
    }
}
