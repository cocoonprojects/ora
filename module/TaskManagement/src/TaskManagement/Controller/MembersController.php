<?php

namespace TaskManagement\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use Application\IllegalStateException;
use Application\DuplicatedDomainEntityException;
use Application\DomainEntityUnavailableException;
use TaskManagement\Service\TaskService;
use TaskManagement\Task;


class MembersController extends HATEOASRestfulController
{
	protected static $collectionOptions = array();
	protected static $resourceOptions = array('DELETE', 'POST');

	/**
	 * 
	 * @var TaskService
	 */
	protected $taskService;

	public function __construct(TaskService $taskService) {
		$this->taskService = $taskService;
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
		
		$this->transaction()->begin();
		try {
			$task->removeMember($this->identity(), $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(200);
		} catch (DomainEntityUnavailableException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(204);	// No content = nothing changed
		} catch (IllegalStateException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(412);	// Preconditions failed
		}
		return $this->response;
	}
	
	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
}