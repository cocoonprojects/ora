<?php

$env = getenv('APPLICATION_ENV') ? : "local";

//$env = "test_local";

//TODO: Get APPLICATION_ENV from shell parameters when use ./doctrine-module command 
/*if ($env == "" || $env == null)
    if (isset($argv[3]))
        $env = $argv[3];
    else
        $env = "local";*/

return array(
    // This should be an array of module namespaces used in the application.
    'modules' => array(
		'DoctrineModule',
        'DoctrineORMModule',
        'Application',
        'TaskManagement',
        'ProjectManagement',
        'ZendOAuth2',
    	'User'	
    ),

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        // This should be an array of paths in which modules reside.
        // If a string key is provided, the listener will consider that a module
        // namespace, the value of that key the specific path to that module's
        // Module class.
        'module_paths' => array(
            __DIR__.'/../module',
            __DIR__.'/../vendor',
        ),

        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => array(
            __DIR__.'/../config/autoload/{,*.}{'.$env.',global}.php',
        ),
    ),
);

