<?php

namespace FOL\Model\Task\Validation\Factory;

use FOL\Model\Task\Validation\Statement\IAnswerStatement;

class DefaultValidationFactory implements IValidationFactory {

    private IAnswerStatement $statement;

    public function __construct(IAnswerStatement $statement) {
        $this->statement = $statement;
    }

    public function validate(array $formData): string {
        return ($this->statement)($formData) ? self::ANSWER_CORRECT : self::ANSWER_INCORRECT;
    }
}
