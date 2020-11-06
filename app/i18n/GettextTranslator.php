<?php

namespace App\Model\Translator;

use Nette\Localization\ITranslator;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class GettextTranslator implements ITranslator {

    public static array $supportedLangs = ['cs', 'en'];
    public static array $locales = [
        'cs' => 'cs_CZ.utf-8',
        'en' => 'en_US.utf-8',
    ];

    public function translate($message, $count = null) {
        if ($message === '' || $message === null) {
            return '';
        }
        if ($count !== null) {
            return ngettext($message, $message, (int)$count);
        } else {
            return gettext($message);
        }
    }

    public static function i18nHelper($object, $field, $lang) {
        return $object->{$field . '_' . $lang};
    }

}
