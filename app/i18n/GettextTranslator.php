<?php

namespace FOL\i18n;

use Nette\Localization\Translator;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class GettextTranslator implements Translator {

    public static array $locales = [
        'cs' => 'cs_CZ.utf-8',
        'en' => 'en_US.utf-8',
        /* 'sk' => 'sk_SK.utf-8',
          'hu' => 'hu_HU.utf-8',
          'pl' => 'pl_PL.utf-8',
          'ru' => 'ru_RU.utf-8',*/
    ];

    public function translate($message, ...$args): string {
        if ($message === '' || $message === null) {
            return '';
        }
        if (isset($args[0])) {
            return ngettext($message, $message, (int)$args[0]);
        } else {
            return gettext($message);
        }
    }

    public static function i18nHelper(object $object, string $field, string $lang): string {
        return $object->{$field . '_' . $lang};
    }

    public static function getSupportedLangs(): array {
        return array_keys(self::$locales);
    }
}
