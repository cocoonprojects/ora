<?php 

namespace TaskManagement\Controller;

use TaskManagement\Service\NotificationService;
use TaskManagement\Service\TaskService;
use Zend\View\Model\JsonModel;
use ZFX\Rest\Controller\HATEOASRestfulController;


class RemindersController extends HATEOASRestfulController
{

	protected static $collectionOptions = ['POST'];
	protected static $resourceOptions = ['POST'];
	
	/**
	 *
	 * @var NotificationService
	 */
	protected $notificationService;
	/**
	 *
	 * @var TaskService
	 */
	protected $taskService;
	/**
	 *
	 * @var \DateInterval
	 */
	protected $intervalForRemindAssignmentOfShares;
	

 	public function __construct(NotificationService $notificationService, TaskService $taskService) {
 		
 		$this->notificationService = $notificationService;
 		$this->taskService = $taskService;
 		$this->intervalForRemindAssignmentOfShares = self::getDefaultIntervalToRemindAssignmentOfShares();
 	}

	/**
	 * @param string $id
	 * @param array $data
	 * @return \Zend\Stdlib\ResponseInterface
	 */
	public function invoke($id, $data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$task = $this->taskService->findTask($id);
		if(is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		switch ($this->params('type')) {
			case "add-estimation":
				if (! $this->isAllowed ( $this->identity (), $task, 'TaskManagement.Reminder.add-estimation' )) {
					$this->response->setStatusCode ( 403 );
					return $this->response;
				}
				
				$receivers = $this->notificationService->remindEstimation($task);
				$this->response->setStatusCode(201);
				$view = new JsonModel();
				$view->setVariables([
					'count' => count($receivers),
					'_embedded' => array_map(function($value) {
						return [
							'id'        => $value->getId(),
							'firstname' => $value->getFirstname(),
							'lastname'  => $value->getLastname(),
							'email'     => $value->getEmail()
						];
					}, $receivers)
				]);
				return $view;
			default:
				$this->response->setStatusCode(404);
		}

		return $this->response;
	}

	/**
	 * Create a new resource
	 *
	 * @param  mixed $data
	 * @return mixed
	 */
	public function create($data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		switch ($this->params('type')) {
			case "assignment-of-shares":
				if(!$this->isAllowed($this->identity(), NULL, 'TaskManagement.Reminder.assignment-of-shares')){
					$this->response->setStatusCode(403);
					return $this->response;
				}

				$tasksToNotify = $this->taskService->findAcceptedTasksBefore($this->getIntervalForRemindAssignmentOfShares());

				foreach ($tasksToNotify as $task){
					$this->notificationService->remindAssignmentOfShares($task);
				}
				$this->response->setStatusCode(201);
				break;
			default:
				$this->response->setStatusCode(404);
		}

		return $this->response;
	}


	public function setIntervalForRemindAssignmentOfShares(\DateInterval $interval){
		$this->intervalForRemindAssignmentOfShares = $interval;
	}
	
	public function getIntervalForRemindAssignmentOfShares(){
		return $this->intervalForRemindAssignmentOfShares;
	}
	
	protected function getCollectionOptions(){
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions(){
		return self::$resourceOptions;
	}
	
	protected static function getDefaultIntervalToRemindAssignmentOfShares(){
		return new \DateInterval('P7D');
	}
}
