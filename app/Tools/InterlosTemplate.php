<?php

namespace FOL\Tools;

use Nette;
use Nette\Application\UI\Template;

final class InterlosTemplate {

    use Nette\SmartObject;

    public static function loadTemplate(Template $template): Template {
        // register custom helpers
        $template->getLatte()->addFilter('timeOnly', Helpers::getHelper('timeOnly'));
        return $template;
    }
}

