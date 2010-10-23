<?php

/**
 * My NApplication bootstrap file.
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */



// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';



// Step 2: Configure environment
// 2a) enable NDebug for better exception and error visualisation
NDebug::enable();

// 2b) load configuration from config.ini file
NEnvironment::loadConfig();



// Step 3: Configure application
// 3a) get and setup a front controller
$application = NEnvironment::getApplication();
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;



// Step 4: Setup application router
$router = $application->getRouter();

$router[] = new NRoute('index.php', array(
	'presenter' => 'Homepage',
	'action' => 'default',
), NRoute::ONE_WAY);

$router[] = new NRoute('<presenter>/<action>/<id>', array(
	'presenter' => 'Homepage',
	'action' => 'default',
	'id' => NULL,
));



// Step 5: Run the application!
$application->run();
