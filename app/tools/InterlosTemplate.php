<?php

namespace App\Tools;

use DataNotFoundException;
use Nette\Application\UI\ITemplate;
use Nette;
use NullPointerException;

final class InterlosTemplate {
    use Nette\SmartObject;

    /**
     * @param ITemplate $template
     * @return ITemplate
     * @throws DataNotFoundException
     * @throws NullPointerException
     */
    public static function loadTemplate(ITemplate $template): ITemplate {
        // register custom helpers
        $template->getLatte()->addFilter("date2", Helpers::getHelper('date')); // this shadows standard Nette helper
        $template->getLatte()->addFilter("time", Helpers::getHelper('time'));
        $template->getLatte()->addFilter("timeOnly", Helpers::getHelper('timeOnly'));
        $template->getLatte()->addFilter("texy", Helpers::getHelper('texy'));

        return $template;
    }
}

