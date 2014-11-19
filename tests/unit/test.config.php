<?php
$env = getenv('APPLICATION_ENV') ? : "local";

return array(
    'modules' => array(
        'DoctrineModule',
        'DoctrineORMModule',
    	'ProophEventStoreModule',
        'Application',
    	'Accounting',
    	'TaskManagement',
        'ZendOAuth2',
    	'User',
    	'Organization',
    	'Kanbanize'
    ),

    'module_listener_options' => array(
        'module_paths' => array(
            __DIR__.'/../../src/module',
            __DIR__.'/../../src/vendor',
        ),
        'config_glob_paths' => array(
            __DIR__.'/../../src/config/autoload/{,*.}{'.$env.',global}.php',
        ),
    ) 
);