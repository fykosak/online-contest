<?php

namespace FOL\Model\Card\Exceptions;

class TaskDoesNotHaveHintException extends CardCannotBeUsedException {

    public function __construct() {
        parent::__construct(_('Task does not have hint'));
    }
}
