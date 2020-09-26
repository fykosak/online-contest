<?php

namespace FOL\Model\Task\Validation\Statement;

class RealCheck implements IAnswerStatement {

    private float $correct;
    private array $tolerance;
    private int $inputIndex;

    public function __construct(float $correct, array $tolerance, int $inputIndex = 0, $options = null) {
        $this->correct = $correct;
        $this->tolerance = $tolerance;
        $this->inputIndex = $inputIndex;
    }

    public function __invoke(array $formData): bool {
        $inputValue = FormAccessor::getValue($this->inputIndex, $formData);
        if ($inputValue < $this->correct - $this->tolerance[0]) {
            return false;
        } elseif ($inputValue > $this->correct + $this->tolerance[0]) {
            return false;
        }
        return true;
    }
}
