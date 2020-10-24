<?php
/**
 * The static class which provides filters to presenters.
 *
 * @author Jan Papousek
 */

namespace App\Tools;

use App\Model\Translator\GettextTranslator;
use DataNotFoundException;
use Nette\SmartObject;
use Texy\Texy;

final class Helpers {
    use SmartObject;

    /** @var Texy */
    private static Texy $texy;

    /**
     * It returns the callback for helper with given name
     * @param string $helper The name of helper.
     * @return callback The callback to the helper.
     * @throws DataNotFoundException if the helper does not exist.
     */
    public static function getHelper(string $helper): callable {
        switch ($helper) {
            case 'date':
                return function (...$args) {
                    return self::dateFormatHelper(...$args);
                };
            case 'time':
                return function (...$args) {
                    return self::timeFormatHelper(...$args);
                };
            case 'translate':
            case 'i18n':
                return function (...$args) {
                    return GettextTranslator::i18nHelper(...$args);
                };
            case 'timeOnly':
                return function (...$args) {
                    return self::timeOnlyHelper(...$args);
                };
            case 'texy':
                return function (...$args) {
                    return self::texyHelper(...$args);
                };
            default:
                throw new DataNotFoundException("helper: $helper");
        }
    }

    /**
     * It returns date in format 'day.month.year'
     *
     * @param $date string Time in format 'YYYY-MM-DD HH:mm:ms'
     * @return string Formated date.
     */
    public static function dateFormatHelper(string $date): string {
        return preg_replace(
            "/(\d{4})-0?([1-9]{1,2}0?)-0?([1-9]{1,2}0?) 0?([0-9]{1,2}0?):(\d{2}):(\d{2})/",
            "\\3. \\2. \\1",
            $date
        );
    }

    /**
     * It returns time in format 'day.month.year, hour:second'
     *
     * @param $time string Time in format 'YYYY-MM-DD HH:mm:ms'
     * @return string Formated time.
     */
    public static function timeFormatHelper(?string $time): string {
        return preg_replace(
            "/(\d{4})-0?([1-9]{1,2}0?)-0?([1-9]{1,2}0?) 0?([0-9]{1,2}0?):(\d{2}):(\d{2})/",
            "\\3. \\2. \\1, \\4:\\5",
            $time
        );
    }

    public static function timeOnlyHelper(?string $time): string {
        return preg_replace(
            "/(\d{4})-0?([1-9]{1,2}0?)-0?([1-9]{1,2}0?) 0?([0-9]{1,2}0?):(\d{2}):(\d{2})/",
            "\\4:\\5:\\6",
            $time
        );
    }

    public static function texyHelper(?string $text): string {
        return self::getTexy()->process($text);
    }

    private static function getTexy(): Texy {
        if (!isset(self::$texy)) {
            self::$texy = new Texy();
            self::$texy->encoding = 'utf8';
        }
        return self::$texy;
    }
}
