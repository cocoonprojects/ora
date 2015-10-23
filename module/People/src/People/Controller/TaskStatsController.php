<?php

namespace People\Controller;

use Application\Controller\OrganizationAwareController;
use Application\Service\UserService;
use People\Service\OrganizationService;
use People\View\TaskStatsJsonModel;
use TaskManagement\Service\TaskService;
use Zend\Validator\Date as DateValidator;

class TaskStatsController extends OrganizationAwareController{
	
	protected static $collectionOptions = [];
	protected static $resourceOptions = ['GET'];
	
	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var UserService
	 */
	private $userService;
	
	public function __construct(OrganizationService $orgService, TaskService $taskService, UserService $userService){
		parent::__construct($orgService);
		$this->taskService = $taskService;
		$this->userService = $userService;
	}
	
	public function get($memberId){

		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		$user = $this->userService->findUser($memberId);
		if(is_null($user)){
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		if(!$this->isAllowed($this->identity(), $user, 'People.User.taskMetrics')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$dateValidator = new DateValidator();

		$endOn = $this->getRequest()->getQuery("endOn");
		$startOn = $this->getRequest()->getQuery("startOn");
		if($dateValidator->isValid($endOn)){
			$endOn = \DateTime::createFromFormat($dateValidator->getFormat(), $endOn);
			$endOn->setTime(23, 59, 59);
		}
		if($dateValidator->isValid($startOn)){
			$startOn = \DateTime::createFromFormat($dateValidator->getFormat(), $startOn);
			$startOn->setTime(0, 0, 0);
		}

		$filters["endOn"] = $endOn;
		$filters["startOn"] = $startOn;

		$stats = $this->taskService->findStatsForMember($this->organization, $memberId, $filters);
		$view = new TaskStatsJsonModel();
		$view->setVariables(['resource' => $stats]);

		return $view;
	}

	protected function getCollectionOptions(){
		return self::$collectionOptions;
	}

	protected function getResourceOptions(){
		return self::$resourceOptions;
	}

	public function getUserService(){
		return $this->userService;
	}

	public function getTaskService(){
		return $this->taskService;
	}
}