<?php
return array(
    'modules' => array(
        'Application',
        'ZendOAuth2',
        'Auth'
    ),

    'module_listener_options' => array(
        'module_paths' => array(
            __DIR__.'/../../src/module',
            __DIR__.'/../../src/vendor',
        ),
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php',
        )
    ) 
);