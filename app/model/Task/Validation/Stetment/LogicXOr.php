<?php

namespace FOL\Model\Task\Validation\Statement;

class LogicXOr implements IAnswerStatement {

    private IAnswerStatement $a;
    private IAnswerStatement $b;

    public function __construct(IAnswerStatement $a, IAnswerStatement $b) {
        $this->a = $a;
        $this->b = $b;
    }

    public function __invoke(array $formData): bool {
        return ($this->a)($formData) xor ($this->b)($formData);
    }
}
