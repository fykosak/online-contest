<?php

namespace FOL\Model\Task\Validation\Statement;

class StringCheck implements IAnswerStatement {

    private string $correct;
    private int $inputIndex;

    public function __construct(string $correct, int $inputIndex = 0, $options = null) {
        $this->correct = $correct;
        $this->inputIndex = $inputIndex;
    }

    public function __invoke(array $formData): bool {
        return FormAccessor::getValue($this->inputIndex, $formData) === $this->correct;
    }
}
