<?php

namespace FOL\Model\Card\Exceptions;

class TaskNotAvailableException extends CardCannotBeUsedException {

    public function __construct() {
        parent::__construct(_('Task is not available'));
    }
}
