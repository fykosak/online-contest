<?php

namespace FOL\Model\Task\Validation\Statement;

class IntCheck implements IAnswerStatement {

    private int $correct;
    private int $inputIndex;

    public function __construct(int $correct, int $inputIndex = 0) {
        $this->correct = $correct;
        $this->inputIndex = $inputIndex;
    }

    public function __invoke(array $formData): bool {
        $value = FormAccessor::getValue($this->inputIndex, $formData);
        if (!is_numeric($value)) {
            return false;
        }
        return +$value === $this->correct;
    }
}
