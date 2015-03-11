<?php
namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Validator\ValidatorChain;
use Zend\Validator\NotEmpty;
use Zend\I18n\Validator\Float;
use Zend\Validator\Between;
use ZendExtension\Mvc\View\ErrorJsonModel;
use Ora\InvalidArgumentException;
use Ora\IllegalStateException;
use Ora\TaskManagement\TaskService;
use Ora\DomainEntityUnavailableException;
use Ora\TaskManagement\Task;
use Ora\Accounting\AccountService;
use Ora\StreamManagement\StreamService;

class SharesController extends AbstractHATEOASRestfulController {
	
	protected static $collectionOptions = array();
	protected static $resourceOptions = array('POST');
	
	/**
	 *
	 * @var TaskService
	 */
	protected $taskService;
	/**
	 * 
	 * @var AccountService
	 */
	protected $accountService;
	
	public function __construct(TaskService $taskService) {
		$this->taskService = $taskService;
	}
	
	public function invoke($id, $data)
	{
		$task = $this->taskService->getTask($id);
		if (is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		$identity = $this->identity();
		if(is_null($identity)) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		$identity = $identity['user'];
		$error = new ErrorJsonModel();
		if(count($data) == 0) {
			$this->transaction()->begin();
			try {
				$task->skipShares($identity);
				$this->onTaskClosed($task);
				$this->transaction()->commit();
				$this->response->setStatusCode(201);
				return $this->response;
			} catch (DomainEntityUnavailableException $e) {
				$this->transaction()->rollback();
				$this->response->setStatusCode(403);
				return $this->response;
			} catch (IllegalStateException $e) {
				$this->transaction()->rollback();
				$this->response->setStatusCode(412);
				return $this->response;
			}
		}
		
		$validator = new ValidatorChain();
		$validator->attach(new NotEmpty(), true)
				  ->attach(new Float(), true)
				  ->attach(new Between(array('min' => 0, 'max' => 100), true));
		
		$total = 0;
		foreach ($data as $key => $value) {
			if($validator->isValid($value)) {
				$total += $value;
			} else {
				$error->addSecondaryErrors($key, $validator->getMessages());
			}
		}
		if($error->hasErrors()) {
			$error->setCode(ErrorJsonModel::$ERROR_INPUT_VALIDATION);
			$this->response->setStatusCode(400);
			return $error;
		}
		
		array_walk($data, function(&$value, $key) {
			$value /= 100;
		});
		
		$this->transaction()->begin();
		try {
			$task->assignShares($data, $identity);
			$this->onTaskClosed($task);
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
			return $this->response;
		} catch (InvalidArgumentException $e) {
			$this->transaction()->rollback();
			$error->setCode(ErrorJsonModel::$ERROR_INPUT_VALIDATION);
			$error->setDescription($e->getMessage());
			$this->response->setStatusCode(400);
		} catch (DomainEntityUnavailableException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(403);
		} catch (IllegalStateException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(412);
		}
		return $error;
	}
	
	public function getTaskService() {
		return $this->taskService;
	}
	
	public function setAccountService(AccountService $accountService) {
		$this->accountService = $accountService;
		return $this;
	}
	
	public function getAccountService() {
		return $this->accountService;
	}
	
	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
	/** TODO: As soon as an event queue is available this piece of code must be part of a component listening to TaskClosed event */
	private function onTaskClosed(Task $task) {
		if($task->getStatus() != Task::STATUS_CLOSED) {
			return;
		}
		if(is_null($this->accountService)) {
			return;
		}
		$credits = $task->getMembersCredits();
		$organizationId = $this->taskService->getTaskOrganization($task);
		
		$this->accountService->transfer($source, $destination, $value, $when);
	}
}