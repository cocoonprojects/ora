<?php
return [
	'jwt' => [
//		'private-key'  => 'file://'.__DIR__.'/../ora.pem',
		'public-key'   => 'file://'.__DIR__.'/../ora.pub',
		'time-to-live' => 'P30D',
		'algorithm'    => 'RS256'
	]
];