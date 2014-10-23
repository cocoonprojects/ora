<?php
$env = getenv('APPLICATION_ENV') ? : "local";

return array(
    'modules' => array(
		'DoctrineModule',
        'DoctrineORMModule',
        'Application',
        'TaskManagement',
        'ProjectManagement',
        'Auth',
        'ZendOAuth2',
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