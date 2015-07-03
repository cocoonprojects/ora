<?php 

use Application\Entity\User;


return [	
		'scheduled-jobs' => [
			'allowTo' => User::ROLE_ADMIN
	]	
];
