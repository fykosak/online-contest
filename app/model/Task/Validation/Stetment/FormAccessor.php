<?php

namespace FOL\Model\Task\Validation\Statement;

abstract class FormAccessor {

    public static function getValue(int $index, array $formData) {
        return $formData[$index];
    }
}
