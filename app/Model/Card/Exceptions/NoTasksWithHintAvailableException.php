<?php

namespace FOL\Model\Card\Exceptions;

class NoTasksWithHintAvailableException extends CardCannotBeUsedException {

    public function __construct() {
        parent::__construct(_('Not tasks with hint available'));
    }
}
