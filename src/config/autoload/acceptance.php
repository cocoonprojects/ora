<?php

return array(
	'service_manager' => array(
		'factories' => array(
			'Application\Service\AdapterResolver' => 'Application\Service\MockOAuth2AdapterResolverFactory',
		),
	),
);