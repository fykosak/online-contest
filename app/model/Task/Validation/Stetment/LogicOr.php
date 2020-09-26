<?php

namespace FOL\Model\Task\Validation\Statement;

class LogicOr extends VariadicStatement {

    protected function evaluate(array $formData): bool {
        foreach ($this->arguments as $argument) {
            if ($argument($formData)) {
                return true;
            }
        }
        return false;
    }
}
