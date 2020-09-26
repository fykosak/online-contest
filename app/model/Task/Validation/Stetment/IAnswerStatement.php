<?php

namespace FOL\Model\Task\Validation\Statement;

interface IAnswerStatement {
    public function __invoke(array $formData): bool;
}
