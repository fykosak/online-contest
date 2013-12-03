<?php
define('LIBS_DIR', '../libs');
define('APP_DIR', '../app');

require_once LIBS_DIR . '/Nette/loader.php';

$loader = new RobotLoader();
$loader->addDirectory(APP_DIR);
$loader->addDirectory(LIBS_DIR);
$loader->register();

// Step 3: Enable Nette\Debug

$debug = Environment::getConfig('debug');

Environment::loadConfig(APP_DIR . '/config/config.ini');
Environment::loadConfig(APP_DIR . '/config/config.local.ini');

// Step 4: Set up the sessions.

// Step 5: Get the front controller
$application = Environment::getApplication();
$application->allowedMethods = FALSE;

// Step 6: Setup application router
$router = $application->getRouter();
//$router[] = FrontendModule::createRouter();
$router[] = new CliRouter(array(
	'action' => 'Cli:default'
));

// Step 7: Connect to the database
dibi::connect(Environment::getConfig("database"));

// Step 9: Run the application!
$application->run();
