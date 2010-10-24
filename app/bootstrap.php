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

Environment::setMode('production', FALSE);


// Step 2: Configure environment
// 2a) enable NDebug for better exception and error visualisation
Debug::enable();

// 2b) load configuration from config.ini file
Environment::loadConfig();



// Step 3: Configure application
// 3a) get and setup a front controller
$application = Environment::getApplication();
$application->errorPresenter = 'Error';

//$application->catchExceptions = TRUE;

require_once('db.php');

dibi::connect(array(
            'driver' => 'mysql',
            'host' => 'porthos.wsolution.cz',
            'username' => $user_mysql,
            'password' => $pass_mysql,
            'database' => $db_mysql,
            //'username' => 'rally_test',
            //'password' => 'hQc5sQqHmJCvQE9Z',
            //'database' => 'rally_test',
            'charset' => 'utf8',
        ));


// Step 4: Setup application router
$router = $application->getRouter();

// mod_rewrite detection
if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
    $router[] = new Route('index.php', array(
                'module' => 'Front',
                'presenter' => 'Default',
                    ), Route::ONE_WAY);


    $router[] = new Route('<module>/<presenter>/<action>/<id>', array(
                'module' => 'Front',
                'presenter' => 'Default',
                'action' => 'default',
                'id' => NULL,
            ));
} else {
    $router[] = new SimpleRouter('Front:Default:default');
}



// Step 5: Run the application!
$application->run();
