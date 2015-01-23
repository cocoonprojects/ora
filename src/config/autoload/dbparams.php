<?php

$env = getenv('APPLICATION_ENV') ? : "local";

$dbParams = array(
		'hostname' => 'localhost',
		'port' => 3306,
		'username' => getenv('DB_USERNAME'),
		'password' => getenv('DB_PASSWORD')
		);
if($env == "local") {
	$dbParams['database'] = 'oraproject';
} else if($env == "acceptance") {
	$dbParams['database'] = 'oraproject_test';
}
