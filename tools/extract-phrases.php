<?php

define('LIB_DIR', dirname(__FILE__) . '/../libs');
define('APP_DIR', dirname(__FILE__) . '/../app');
$poTarget = APP_DIR . '/i18n/locale/cs/LC_MESSAGES/messages.po';

require_once LIB_DIR . '/gettext-extractor/NetteGettextExtractor.php';

$ge = new NetteGettextExtractor();

$ge->setupForms();
$ge->setupDataGrid();
		
$ge->scan(APP_DIR);

$ge->save($poTarget);

