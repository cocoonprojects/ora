<?php

namespace TaskManagement\Controller;

use Application\Controller\OrganizationAwareController;
use Application\DomainEntityUnavailableException;
use Application\IllegalStateException;
use Application\View\ErrorJsonModel;
use People\Service\OrganizationService;
use TaskManagement\Service\TaskService;
use TaskManagement\View\TaskJsonModel;
use Zend\I18n\Validator\Float;
use Zend\Permissions\Acl\Acl;
use Zend\Validator\GreaterThan;
use Zend\Validator\NotEmpty;
use Zend\Validator\ValidatorChain;

/**
 * EstimationsController
 *
 */
class EstimationsController extends OrganizationAwareController {

	protected static $collectionOptions = array();
	protected static $resourceOptions = array('POST');

	/**
	 * @var TaskService
	 */
	protected $taskService;
	/**
	 * @var Acl
	 */
	private $acl;

	public function __construct(OrganizationService $organizationService, TaskService $taskService, Acl $acl) {
		parent::__construct($organizationService);
		$this->taskService = $taskService;
		$this->acl = $acl;
	}
	
	public function invoke($id, $data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

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
			$this->response->setStatusCode (400);
			return $error;
		}
		
		$task = $this->taskService->getTask($id);
		if (is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		$this->transaction()->begin();
		try {
			$task->addEstimation($value, $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
			$view = new TaskJsonModel($this->url(), $this->identity(), $this->acl, $this->organization);
			$view->setVariable('resource', $task);
			return $view;
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