<?php

namespace TaskManagement\Controller;

use TaskManagement\View\TaskJsonModel;
use ZFX\Rest\Controller\HATEOASRestfulController;
use Application\IllegalStateException;
use Application\DuplicatedDomainEntityException;
use Application\DomainEntityUnavailableException;
use TaskManagement\Service\TaskService;
use Application\Service\UserService;
use TaskManagement\Task;


class OwnerController extends HATEOASRestfulController
{
	protected static $resourceOptions = ['POST'];

	/**
	 * 
	 * @var TaskService
	 */
	protected $taskService;

	protected $userService;

	public function __construct(TaskService $taskService, UserService $userService) {
		$this->taskService = $taskService;
		$this->userService = $userService;
	}
	
	public function invoke($id, $data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$task = $this->taskService->getTask($id);
		if (is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		$ownerId = $data['ownerId'];
		// $ownerId = '20000000-0000-0000-0000-000000000000';
		$user = $this->userService->findUser($ownerId);
		if(is_null($user)){
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		$this->transaction()->begin();
		try {
			$task->changeOwner($user, $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
			$view = new TaskJsonModel($this);
			$view->setVariable('resource', $task);
			return $view;
		} catch (DomainEntityUnavailableException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(404);
		} catch (MissingOrganizationMembershipException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(412);	// Preconditions failed
		}
		return $this->response;
	}
	
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
}