<?php
namespace TaskManagement\Controller;

use Zend\Validator\ValidatorChain;
use Zend\Validator\NotEmpty;
use Zend\I18n\Validator\Float;
use Zend\Validator\Between;
use Application\Controller\AbstractHATEOASRestfulController;
use Application\View\ErrorJsonModel;
use Application\InvalidArgumentException;
use Application\IllegalStateException;
use Application\DomainEntityUnavailableException;
use TaskManagement\Service\TaskService;
use TaskManagement\Task;
use TaskManagement\StreamService;

class SharesController extends AbstractHATEOASRestfulController {
	
	protected static $collectionOptions = array();
	protected static $resourceOptions = array('POST');
	
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
				$this->transaction()->commit();
				$this->response->setStatusCode(201);
				return $this->response;
			} catch (DomainEntityUnavailableException $e) {
				$this->transaction()->rollback();
				$this->response->setStatusCode(403);
				return $this->response;
			} catch (IllegalStateException $e) {
				$error->setCode(412);
				$error->setDescription($e->getMessage());
				$this->transaction()->rollback();
				$this->response->setStatusCode(412);
				return $error;
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
			$error->setCode(412);
			$error->setDescription($e->getMessage());
			$this->transaction()->rollback();
			$this->response->setStatusCode(412);
		}
		return $error;
	}
	
	public function getTaskService() {
		return $this->taskService;
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