<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Authentication\AuthenticationService;
use Ora\TaskManagement\TaskService;
use Ora\TaskManagement\Task;
use Ora\StreamManagement\StreamService;
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
	 * @var StreamService
	 */
	protected $streamService;

	/**
	 *
	 * @var Task
	 */
	protected $task = null;
	
	public function __construct(TaskService $taskService, AuthenticationServiceInterface $authService, StreamService $streamService) {
		$this->authService = $authService;
		$this->taskService = $taskService;
		$this->streamService = $streamService;
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
		if(!isset($data['value'])) {
			$this->response->setStatusCode(400);
			return $this->response;
		}
		try {
	
	
			$loggedUser = $this->authService->getIdentity()['user'];
			
			//TODO check if the value is numeric
			$value = $data['value'];

			$validator_NotEmpty = new \Zend\Validator\NotEmpty ();
			$validator_float = new \Zend\I18n\Validator\Float();
			$validator_GT = new \Zend\Validator\GreaterThan();
			$validator_GT->setMin(0);
			$validator_eq = new \Zend\Validator\Between();
			$validator_eq->setMin(-1);
			$validator_eq->setMax(-1);
			
			
			if (! $validator_NotEmpty->isValid ( $value ) || !$validator_float->isValid($value) || (!$validator_GT->isValid($value) && !$validator_eq->isValid($value))) {
				// request not correct
				$this->response->setStatusCode ( 400 );
				return $this->response;
			}
			
			$this->task->addEstimation($loggedUser, $value);
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