<?php

namespace FOL\Model\Task\Validation\Statement;

class LogicAnd extends VariadicStatement {

    protected function evaluate($formData): bool {
        foreach ($this->arguments as $argument) {
            if (!$argument($formData)) {
                return false;
            }
        }
        return true;
    }
}
