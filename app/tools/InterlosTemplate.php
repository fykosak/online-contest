<?php

namespace App\Tools;

use Nette\Application\UI\ITemplate;

final class InterlosTemplate
{

    final private function  __construct() {}

    public static function loadTemplate(ITemplate $template) {
	// register custom helpers
	$template->registerHelper("date2", Helpers::getHelper('date')); // this shadows standard Nette helper
	$template->registerHelper("time", Helpers::getHelper('time'));
	$template->registerHelper("timeOnly", Helpers::getHelper('timeOnly'));
	$template->registerHelper("texy", Helpers::getHelper('texy'));
        
	return $template;
    }
}

