<?php

use Kanbanize\Service\KanbanizeAPI;

return array(
		'service_manager' => array(
				'factories' => array(
						'Kanbanize\KanbanizeAPI' => function ($locator) {
							$mockGenerator = new \PHPUnit_Framework_MockObject_Generator();
							return $mockGenerator->getMock(KanbanizeAPI::class, ['getProjectsAndBoards', 'getBoardStructure']);
						},
				),
		),
);