<?php

$env = getenv('APPLICATION_ENV') ? : "local";

$dbParams = array(
		'hostname' => 'localhost',
		'port' => 3306,
		'username' => 'ora',
		'password' => 'ora_DB!'
		);
if($env == "local") {
	$dbParams['database'] = 'oraproject';
} else if($env == "acceptance") {
	$dbParams['database'] = 'oraproject_test';
}