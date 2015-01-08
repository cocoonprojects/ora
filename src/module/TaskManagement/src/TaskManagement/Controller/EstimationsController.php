<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\TaskManagement\TaskService;
use Ora\DuplicatedDomainEntityException;
use Ora\IllegalStateException;
use Ora\DomainEntityUnavailableException;
use Zend\Validator\ValidatorChain;
use Zend\Validator\NotEmpty;
use Zend\I18n\Validator\Float;
use Zend\Validator\GreaterThan;
use Zend\Validator\Identical;

/**
 * EstimationController
 *
 * @author Andrea Lupia
 *
 * @version
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
		if(!isset($data['value'])) {
			$this->response->setStatusCode(400);
			return $this->response;
		}
		//TODO check if the value is numeric in a localized way
		$value = $data['value'];
		
		$validator = new ValidatorChain();
		$validator->attach(new NotEmpty(), true)
				  ->attach(new Float(), true)
				  ->attach(new GreaterThan(['min' => 0, 'inclusive' => true]), true);
		
		if (! ($validator->isValid ( $value ) || $value == -1)) {
			// request not correct
			$this->response->setStatusCode ( 400 );
			return $this->response;
		}
			
		$task = $this->taskService->getTask($id);
		if (is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		$loggedUser = $this->identity()['user'];
		$this->transaction()->begin();
		try {
			$task->addEstimation($value, $loggedUser);
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
		} catch (DuplicatedDomainEntityException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(204);
		} catch (DomainEntityUnavailableException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(401);// Unauthorized
		} catch (IllegalStateException $e) {
			$this->transaction()->rollback();
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