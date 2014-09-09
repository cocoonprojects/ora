<?php
return array(
    'modules' => array(
        'Application',
        'ZendOAuth2'
    ),

    'module_listener_options' => array(
        'module_paths' => array(
            __DIR__.'/../../src/module',
            __DIR__.'/../../vendor',
        ),
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php',
        )
    )
);