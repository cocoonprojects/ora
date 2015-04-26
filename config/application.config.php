<?php

$env = getenv('APPLICATION_ENV') ? : "local";

return array(
	// This should be an array of module namespaces used in the application.
	'modules' => array(
		'DoctrineModule',
		'DoctrineORMModule',
		'ProophEventStoreModule',
		'ZendOAuth2',
		'AssetManager',
		'BjyAuthorize',
		'Application',
		'Accounting',
		'TaskManagement',
		'People',
		'Kanbanize'
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
			__DIR__.'/autoload/{,*.}{global,'.$env.',local}.php',
		),
	),		
);