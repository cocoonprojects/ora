<?php
return [
	'jwt' => [
		'private-key'  => getenv('PRIVATE_KEY_PATH'),
		'public-key'   => getenv('PUBLIC_KEY_PATH'),
		'time-to-live' => 'P30D',
		'algorithm'    => 'RS256'
	]
];