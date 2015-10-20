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

		$endOn = (new \DateTime())->setTime(23, 59, 59);
		if($dateValidator->isValid($this->getRequest()->getQuery("endOn"))){
			$endOn = \DateTime::createFromFormat($dateValidator->getFormat(), $this->getRequest()->getQuery("endOn"));
		}
		if($dateValidator->isValid($this->getRequest()->getQuery("startOn"))){
			$startOn = \DateTime::createFromFormat($dateValidator->getFormat(), $this->getRequest()->getQuery("startOn"));
		}else{
			$startOn = $this->getDefaultStartOn($endOn);
		}

		$filters["endOn"] = $endOn->format("Y-m-d H:i:s");
		$filters["startOn"] = $startOn->format("Y-m-d H:i:s");

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

	private function getDefaultStartOn(\DateTime $endOn){
		$startOn = clone $endOn;
		return $startOn->sub(new \DateInterval('P1Y'))->setTime(0, 0, 0);
	}
}