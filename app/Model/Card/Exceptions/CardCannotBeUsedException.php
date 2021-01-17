<?php

namespace FOL\Model\Card\Exceptions;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

abstract class CardCannotBeUsedException extends BadRequestException {

    public function __construct(string $message) {
        parent::__construct($message, Response::S400_BAD_REQUEST);
    }
}
