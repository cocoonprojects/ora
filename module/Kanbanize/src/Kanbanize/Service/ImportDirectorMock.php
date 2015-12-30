<?php

namespace Kanbanize\Service;

use People\Organization;
use Application\Entity\User;

class ImportDirectorMock extends ImportDirector{
	
	private $projects = [];
	private $apiKeys = [
		"AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA",
		"BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB",
		"DDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDD"
	];
	private $subdomains = [
		"foo",
		"acme"
	];
	
	public function __construct($projects){
		$this->projects = $projects;
	}
	
	public function import(Organization $organization, User $requestedBy){
		$importResult = [
				"createdStreams" => 1,
				"updatedStreams" => 0,
				"createdTasks" => 1,
				"updatedTasks" => 1,
				"deletedTasks" => 0,
				"errors" => ["Cannot update task {taskId: 1, boardId: 1, projectId: 1 due to Missing mapping for column Backlog"]
		];
		$this->getEventManager()->trigger(self::IMPORT_COMPLETED, [
				'importResult' => $importResult,
				'organizationId' => $organization->getId()
		]);
		return $importResult;
	}

	public function importProjects(Organization $organization, User $requestedBy, $apiKey, $subdomain){
		if(in_array($apiKey, $this->apiKeys)){
			if(in_array($subdomain, $this->subdomains)){
				return $this->projects;
			}
			return [
					'errors' => "Cannot import projects due to problem with call: Could not resolve host: .kanbanize.com"
			];
		}
		return [
				'errors' => "Cannot import projects due to: The request cannot be processed. Please make sure you've specified all input parameters correctly"
		];
	}
	
	public function importBoardColumns(Organization $organization, User $requestedBy, $boardId){
		return [
			'columns' => [
				'Requested' => '',
				'Approved' => '',
				'WIP' => 20,
				'Testing' => 20,
				'Production Release' => 30,
				'Accepted' => 40,
				'Closed' => 50,
				'Archive' => 50
			]
		];
	}
	
	public function importBoardStructure(Organization $organization, User$requestedBy, $boardId) {
		return [ 
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
		];
	}
	
	private function initApi($apiKey, $subdomain){
		return new KanbanizeAPI();
	}
}