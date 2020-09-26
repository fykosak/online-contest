<?php

namespace FOL\Model\Task\Validation\Factory;

interface IValidationFactory {

    public const ANSWER_CORRECT = 'correct';
    public const ANSWER_INCORRECT = 'incorrect';
    public const ANSWER_PENDING = 'pending';

    public function validate(array $formData): string;
}
