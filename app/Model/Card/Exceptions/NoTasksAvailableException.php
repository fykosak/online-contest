<?php

namespace FOL\Model\Card\Exceptions;

class NoTasksAvailableException extends CardCannotBeUsedException {

    public function __construct() {
        parent::__construct(_('Not tasks available'));
    }
}
