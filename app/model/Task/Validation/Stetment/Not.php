<?php

namespace FOL\Model\Task\Validation\Statement;

class Not implements IAnswerStatement {

    private IAnswerStatement $expression;

    public function __construct(IAnswerStatement $expression) {
        $this->expression = $expression;
    }

    final public function __invoke(array $formData): bool {
        return !($this->expression)($formData);
    }
}
