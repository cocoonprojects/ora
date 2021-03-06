<?php

use Application\Authentication\OAuth2\UserServiceOAuth2AdapterMock;

return array(
	'service_manager' => array(
		'factories' => array(
			'Application\Service\AdapterResolver' => function ($locator) {
				$userService = $locator->get('Application\UserService');
				return new UserServiceOAuth2AdapterMock($userService);
			},
		),
	),
);