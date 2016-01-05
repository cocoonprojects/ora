<?php

use Kanbanize\Service\KanbanizeAPI;

return array(
		'service_manager' => array(
				'factories' => array(
						'Kanbanize\KanbanizeAPI' => function ($locator) {
							$mockGenerator = new \PHPUnit_Framework_MockObject_Generator();
							$clientMock = $mockGenerator->getMock(KanbanizeAPI::class, ['getProjectsAndBoards', 'getBoardStructure']);
							$clientMock->expects(\PHPUnit_Framework_TestCase::once())
								->method('getProjectsAndBoards')
								->willReturn(
									[
											[
												'name' => 'foo project',
												'boards' => [
													[
														"name" => "board 1",
														"id" => 1
													]
												]
											]
									]
								);
							$clientMock->expects(\PHPUnit_Framework_TestCase::once())
								->method('getBoardStructure')
								->willReturn(
									[ 
											"columns" => [ 
												[ 
													"position" => "0",
													"lcname" => "Requested",
													"description" => "",
													"tasksperrow" => "1" 
												],
												[ 
													"position" => "1",
													"lcname" => "Approved",
													"description" => "",
													"tasksperrow" => "1" 
												],
												[ 
													"position" => "2",
													"lcname" => "WIP",
													"description" => "",
													"tasksperrow" => "1" 
												],
												[ 
													"position" => "3",
													"lcname" => "Testing",
													"description" => "",
													"tasksperrow" => "1" 
												],
												[ 
													"position" => "4",
													"lcname" => "Production Release",
													"description" => "",
													"tasksperrow" => "1" 
												],
												[ 
													"position" => "5",
													"lcname" => "Accepted",
													"description" => "",
													"tasksperrow" => "1" 
												],
												[ 
													"position" => "6",
													"lcname" => "Closed",
													"description" => "",
													"tasksperrow" => "1" 
												],
												[ 
													"position" => "7",
													"lcname" => "Archive",
													"description" => "",
													"tasksperrow" => "0" 
												]
											]
									]
							);
							return $clientMock;
						},
				),
		),
);