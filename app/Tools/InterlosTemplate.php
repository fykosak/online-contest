<?php

namespace FOL\Tools;

use DataNotFoundException;
use Nette;
use Nette\Application\UI\Template;

final class InterlosTemplate {
    use Nette\SmartObject;

    /**
     * @param  Template  $template
     * @return  Template
     * @throws DataNotFoundException
     */
    public static function loadTemplate( Template  $template): Template {
        // register custom helpers
        $template->getLatte()->addFilter('date2', Helpers::getHelper('date')); // this shadows standard Nette helper
        $template->getLatte()->addFilter('time', Helpers::getHelper('time'));
        $template->getLatte()->addFilter('timeOnly', Helpers::getHelper('timeOnly'));
        $template->getLatte()->addFilter('texy', Helpers::getHelper('texy'));

        return $template;
    }
}

