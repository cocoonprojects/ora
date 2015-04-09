<?php

namespace TaskManagement\Controller;

use Zend\Validator\ValidatorChain;
use Zend\Validator\NotEmpty;
use Zend\Validator\GreaterThan;
use Zend\Validator\Identical;
use Zend\I18n\Validator\Float;
use Application\Controller\AbstractHATEOASRestfulController;
use Application\DuplicatedDomainEntityException;
use Application\IllegalStateException;
use Application\DomainEntityUnavailableException;
use Application\View\ErrorJsonModel;
use TaskManagement\Service\TaskService;

/**
 * EstimationsController
 *
 */
class EstimationsController extends AbstractHATEOASRestfulController {

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
		$error = new ErrorJsonModel();
		if(!isset($data['value'])) {
			$error->setCode(400);
			$error->addSecondaryErrors('value', ['Value cannot be empty']);
			$error->setDescription('Specified values are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}
		
		//TODO check if the value is numeric in a localized way
		$value = $data['value'];
		
		$validator = new ValidatorChain();
		$validator->attach(new NotEmpty(), true)
				  ->attach(new Float(), true)
				  ->attach(new GreaterThan(['min' => 0, 'inclusive' => true]), true);
		
		if (! ($validator->isValid ( $value ) || $value == -1)) {
			$error->setCode(400);
			$error->addSecondaryErrors('value', $validator->getMessages());
			$error->setDescription('Specified values are not valid');
			$this->response->setStatusCode ( 400 );
			return $error;
		}
		
		$task = $this->taskService->getTask($id);
		if (is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		$loggedUser = $this->identity()['user'];
		
		$this->transaction()->begin();
		try {
			$task->addEstimation($value, $loggedUser);
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
		} catch (DomainEntityUnavailableException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(403);	// Forbidden because not a member
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