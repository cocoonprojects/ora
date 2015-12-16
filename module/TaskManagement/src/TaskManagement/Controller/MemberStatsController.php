<?php

namespace TaskManagement\Controller;

use Application\Controller\OrganizationAwareController;
use Application\Service\UserService;
use People\Service\OrganizationService;
use TaskManagement\Service\TaskService;
use Zend\Validator\Date;
use Zend\View\Model\JsonModel;

class MemberStatsController extends OrganizationAwareController{
	
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

	public function get($id)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		if(!$this->isAllowed($this->identity(), $this->organization, 'TaskManagement.Task.stats')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$user = $this->userService->findUser($id);
		if(is_null($user)){
			$this->response->setStatusCode(404);
			return $this->response;
		}

		$filters = [];
		$dateValidator = new Date();
		$endOn = $this->getRequest()->getQuery("endOn");
		if($dateValidator->isValid($endOn)){
			$endOn = \DateTime::createFromFormat($dateValidator->getFormat(), $endOn);
			$endOn->setTime(23, 59, 59);
			$filters["endOn"] = $endOn;
		}
		$startOn = $this->getRequest()->getQuery("startOn");
		if($dateValidator->isValid($startOn)){
			$startOn = \DateTime::createFromFormat($dateValidator->getFormat(), $startOn);
			$startOn->setTime(0, 0, 0);
			$filters["startOn"] = $startOn;
		}
		$stats = $this->taskService->findMemberStats($this->organization, $id, $filters);
		return new JsonModel($stats);
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