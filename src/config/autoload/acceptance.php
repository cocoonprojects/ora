<?php

use ZendExtension\Authentication\OAuth2\MockOAuth2Adapter;

return array(
	'service_manager' => array(
		'factories' => array(
			'Application\Service\AdapterResolver' => function ($locator) {
				$userService = $locator->get('Application\UserService');
				return new MockOAuth2Adapter($userService);
			},
		),
	),
);