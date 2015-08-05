<?php
return [
	'jwt' => [
		'private-key'  => 'file:///var/www/ora/config/ora.pem',
		'public-key'   => 'file:///var/www/ora/config/ora.pub',
		'time-to-live' => 'P30D',
		'algorithm'    => 'RS256'
	]
];