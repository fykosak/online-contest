<?php

namespace FOL\Tools;

use Fykosak\Utils\Localization\GettextTranslator;
use InvalidArgumentException;
use Nette\SmartObject;

/**
 * The static class which provides filters to presenters.
 *
 * @author Jan Papousek
 */
final class Helpers {

    use SmartObject;

    /**
     * It returns the callback for helper with given name
     * @param string $helper The name of helper.
     * @return callback The callback to the helper.
     */
    public static function getHelper(string $helper): callable {
        switch ($helper) {
            case 'translate':
            case 'i18n':
                return function (...$args) {
                    return GettextTranslator::i18nHelper(...$args);
                };
            default:
                throw new InvalidArgumentException("helper: $helper");
        }
    }
}
