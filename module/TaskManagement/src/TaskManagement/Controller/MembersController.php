<?php

namespace TaskManagement\Controller;

use TaskManagement\View\TaskJsonModel;
use ZFX\Rest\Controller\HATEOASRestfulController;
use Application\IllegalStateException;
use Application\DuplicatedDomainEntityException;
use Application\DomainEntityUnavailableException;
use TaskManagement\Service\TaskService;
use Application\Controller\OrganizationAwareController;
use Application\Service\UserService;
use People\Service\OrganizationService;
use TaskManagement\Task;


class MembersController extends OrganizationAwareController
{
	protected static $resourceOptions = ['DELETE', 'POST'];

	/**
	 * 
	 * @var TaskService
	 */
	protected $taskService;
	protected $orgService;

	public function __construct(OrganizationService $orgService, TaskService $taskService, UserService $userService){
		parent::__construct($orgService);
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
		
		$this->transaction()->begin();
		try {
			$task->addMember($this->identity(), Task::ROLE_MEMBER);
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
			$view = new TaskJsonModel($this);
			$view->setVariable('resource', $task);
			return $view;
		} catch (DuplicatedDomainEntityException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(204);
		} catch (IllegalStateException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(412);	// Preconditions failed
		}
		return $this->response;
	}

	public function delete($id)
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
		
		$memberToRemove = $this->identity();
		try {
			$memberId = $this->getRequest()->getQuery("member");
			if ($this->identity()->isOwnerOf($this->organization) 
				&& !empty($memberId) 
				&& ($member=$this->userService->findUser($this->getRequest()->getQuery("member")))
				&& preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $member->getId())!==false
				) {
					$memberToRemove = $member;
			}
		} catch (DomainEntityUnavailableException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(204);	// No content = nothing changed
		}

		$this->transaction()->begin();
		try {
			$task->removeMember($memberToRemove, $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(200);
			$view = new TaskJsonModel($this);
			$view->setVariable('resource', $task);
			return $view;
		} catch (DomainEntityUnavailableException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(204);	// No content = nothing changed
		} catch (IllegalStateException $e) {
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