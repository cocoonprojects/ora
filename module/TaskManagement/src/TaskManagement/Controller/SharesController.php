<?php
namespace TaskManagement\Controller;

use Application\DomainEntityUnavailableException;
use Application\IllegalStateException;
use Application\InvalidArgumentException;
use Application\View\ErrorJsonModel;
use TaskManagement\Service\TaskService;
use TaskManagement\View\TaskJsonModel;
use Zend\I18n\Validator\IsFloat;
use Zend\Validator\Between;
use Zend\Validator\NotEmpty;
use Zend\Validator\ValidatorChain;
use ZFX\Rest\Controller\HATEOASRestfulController;

class SharesController extends HATEOASRestfulController {
	
	protected static $resourceOptions = ['POST'];
	
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

		$error = new ErrorJsonModel();
		if(count($data) == 0) {
			$this->transaction()->begin();
			try {
				$task->skipShares($this->identity());
				$this->transaction()->commit();
				$this->response->setStatusCode(201);
				$view = new TaskJsonModel($this);
				$view->setVariable('resource', $task);
				return $view;
			} catch (DomainEntityUnavailableException $e) {
				$this->transaction()->rollback();
				$this->response->setStatusCode(403);
				$error->setCode(403);
				$error->setDescription($e->getMessage());
				return $error;
			} catch (IllegalStateException $e) {
				$this->transaction()->rollback();
				$this->response->setStatusCode(412);
				$error->setCode(412);
				$error->setDescription($e->getMessage());
				return $error;
			}
		}
		
		$validator = new ValidatorChain();
		$validator->attach(new NotEmpty(), true)
				  ->attach(new IsFloat(), true)
				  ->attach(new Between(['min' => 0, 'max' => 100], true));
		
		foreach ($data as $key => $value) {
			if(!$validator->isValid($value)) {
				$error->addSecondaryErrors($key, $validator->getMessages());
			}
		}
		if($error->hasErrors()) {
			$error->setCode(ErrorJsonModel::$ERROR_INPUT_VALIDATION);
			$this->response->setStatusCode(422);
			return $error;
		}
		
		array_walk($data, function(&$value) {
			$value /= 100;
		});
		
		$this->transaction()->begin();
		try {
			$task->assignShares($data, $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
			$view = new TaskJsonModel($this);
			$view->setVariable('resource', $task);
			return $view;
		} catch (InvalidArgumentException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(422);
			$error->setCode(ErrorJsonModel::$ERROR_INPUT_VALIDATION);
			$error->setDescription($e->getMessage());
		} catch (DomainEntityUnavailableException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(403);
			$error->setCode(403);
			$error->setDescription($e->getMessage());
		} catch (IllegalStateException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(412);
			$error->setCode(412);
			$error->setDescription($e->getMessage());
		}
		return $error;
	}
	
	public function getTaskService() {
		return $this->taskService;
	}
	
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
}