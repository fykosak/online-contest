<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class GettextTranslator implements ITranslator {
    public static $supportedLangs = array('cs', 'en');
    public static $locales = array(
        'cs' => 'cs_CZ.utf-8',
        'en' => 'en_US.utf-8',
    );


    public function translate($message, $count = NULL) {
        if($message === "" || $message === null){
            return "";
        }
        if ($count !== null) {
            return ngettext($message, $message, (int)$count);
        } else {
            return gettext($message);
        }
    }

}
