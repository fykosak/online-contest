<?php

namespace FOL\Model\Task\Validation\Statement;

abstract class VariadicStatement implements IAnswerStatement {
    /** @var IAnswerStatement[] */
    protected array $arguments;

    public function __construct(...$args) {
        $this->arguments = $args;
    }

    final public function __invoke(array $formData): bool {
        return $this->evaluate($formData);
    }

    abstract protected function evaluate(array $formData): bool;
}
