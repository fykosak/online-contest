<?php

namespace FOL\Model\Card;

use Nette\Forms\Container;

abstract class SingleFormCard extends Card {

    abstract public function decorateFormContainer(Container $container, string $lang): void;
}
