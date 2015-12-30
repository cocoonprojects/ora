<?php

use Kanbanize\Service\ImportDirectorMock;


return array(
		'service_manager' => array(
				'factories' => array(
						'Kanbanize\ImportDirector' => function ($locator) {
							$projects = [
								[
									'name' => 'foo project',
									'boards' => [
										[
											"name" => "board 1",
											"id" => 1
										]
									]
								]
							];
							return new ImportDirectorMock($projects);
						},
				),
		),
);