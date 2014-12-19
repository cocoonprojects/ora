<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Authentication\AuthenticationService;
use Ora\TaskManagement\TaskService;
use Ora\TaskManagement\Task;
use Ora\ProjectManagement\ProjectService;
use Ora\ReadModel\Estimation;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\View\Model\JsonModel;
use Ora\DuplicatedDomainEntityException;
use Ora\IllegalStateException;
use Ora\DomainEntityUnavailableException;

/**
 * EstimationController
 *
 * @author Andrea Lupia
 *
 * @version
 *
 */
class EstimationController extends AbstractHATEOASRestfulController {

	protected static $collectionOptions = array('POST');
	protected static $resourceOptions = array();

	/**
	 *
	 * @var TaskService
	 */
	protected $taskService;
	
	/**
	 *
	 * @var AuthenticationService
	 */
	protected $authService;
	
	/**
	 *
	 * @var ProjectService
	 */
	protected $projectService;

	/**
	 *
	 * @var Task
	 */
	protected $task = null;
	
	public function __construct(TaskService $taskService, AuthenticationServiceInterface $authService, ProjectService $projectService) {
		$this->authService = $authService;
		$this->taskService = $taskService;
		$this->projectService = $projectService;
	}
	
	public function preDispatch($e)
	{
		if (null !== $this->params()->fromRoute('taskId'))
		{
			$id = $this->params()->fromRoute('taskId');
			$this->task = $this->taskService->getTask($id);
			if (is_null($this->task))
			{
				$this->response->setStatusCode(404);
				return $this->response;
			}
		}
	}
	
	public function create($data)
	{
		try {
	
			$loggedUser = $this->authService->getIdentity()['user'];
	
			$projectId = $this->task->getProjectId();
	
			$project = $this->projectService->getProject($projectId);

			if(is_null($project)) {
				// Project Not Found
				echo "Project not found";
				$this->response->setStatusCode(404);
				return $this->response;
			}

			//FIXME
			
			$this->task->addEstimation($loggedUser, $data['value']);
			$this->taskService->editTask($this->task);
			$this->response->setStatusCode(201);
		} catch (DuplicatedDomainEntityException $e) {
			$this->response->setStatusCode(204);
		} catch (DomainEntityUnavailableException $e) {
			$this->response->setStatusCode(401);// Unauthorized
		} catch (IllegalStateException $e) {
			$this->response->setStatusCode(406);	// Not acceptable
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