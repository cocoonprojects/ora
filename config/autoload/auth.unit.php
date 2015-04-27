<?php

use ZFX\Authentication\AuthenticationServiceMock;

return array(
	'service_manager' => array(
		'invokables' => array(
			'Zend\Authentication\AuthenticationService' => AuthenticationServiceMock::class
		),
	),
);