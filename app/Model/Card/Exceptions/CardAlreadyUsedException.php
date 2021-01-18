<?php

namespace FOL\Model\Card\Exceptions;

class CardAlreadyUsedException extends CardCannotBeUsedException {

    public function __construct() {
        parent::__construct(_('Card already used'));
    }

}
