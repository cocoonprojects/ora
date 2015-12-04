<?php

use Kanbanize\Service\KanbanizeServiceImpl;
use Kanbanize\KanbanizeTask;

return array(
		'service_manager' => array(
				'factories' => array(
						'Kanbanize\KanbanizeService' => function ($locator) {
							$entityManager = $locator->get('doctrine.entitymanager.orm_default');
							return new KanbanizeServiceImpl($entityManager);
						},
				),
		),
);