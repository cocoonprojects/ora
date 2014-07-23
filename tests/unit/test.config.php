<?php
return array(
    'modules' => array(
        'Application',
    ),

    'module_listener_options' => array(
        'module_paths' => array(
            __DIR__.'/../../src/module',
            './vendor',
        ),
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php',
        )
    )
);