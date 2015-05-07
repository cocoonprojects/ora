<?php 

namespace TaskManagement\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use TaskManagement\Service\NotificationService;
use TaskManagement\Service\TaskService;
use TaskManagement\Entity\Task;

class RemindersController extends HATEOASRestfulController
{

	protected static $collectionOptions = ['POST'];
	protected static $resourceOptions = [];
	
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
	protected $intervalForRemindShareAssignment;
	
 	public function __construct(NotificationService $notificationService, TaskService $taskService) {
 		$this->notificationService = $notificationService;
 		$this->taskService = $taskService;
 	}
	
	/**
	 * Create a new reminder
	 * @method POST
	 * @link http://oraproject/task-management/tasks/reminders/
	 * @param array $data['reminder'] 
	 * @return HTTPStatusCode
	 * 
	 */
	public function create($data){
		
		//TODO: spostare il controllo degli accessi nelle asserzioni
		if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != 'localhost'){
			$this->response->setStatusCode(404);
			return $this->response;
		}
		
		
		if (!isset($data['reminder']) || $data['reminder'] == ''){
			$this->response->setStatusCode(400);
			return $this->response;
		}

		switch ($data['reminder']) {
			case "assignment-of-shares":
				
				$tasksToNotify = $this->taskService->findAcceptedTasksBefore($this->getIntervalForRemindShareAssignment());

				if(is_array($tasksToNotify) && count($tasksToNotify) > 0){
						
					array_map(array($this, 'remindShareAssignmentOnTask'),  $tasksToNotify);
				}
				break;
			default:
				$this->response->setStatusCode(405);
				break;
		}

		return $this->response;
	}
	
	
	private function remindShareAssignmentOnTask(Task $taskToNotify){
		
		$taskMembersWithEmptyShares = $this->taskService->findMembersWithEmptyShares($taskToNotify);		
		foreach ($taskMembersWithEmptyShares as $member){
			
			$params = array(
					'name' => $member->getFirstname()." ".$member->getLastname(),					
					'taskSubject' => $taskToNotify->getSubject(),
					'taskId' => $taskToNotify->getId(),
					'emailAddress' => $member->getEmail(),					
					'url' => 'http://'.$_SERVER['SERVER_NAME'].'/task-management#'.$taskToNotify->getId()
			);
			
			$this->notificationService->sendEmailNotificationForShareAssignment($params);
		}
	}
	
	public function setIntervalForRemindShareAssignment(\DateInterval $interval){
		$this->intervalForRemindShareAssignment = $interval;
	}
	
	public function getIntervalForRemindShareAssignment(){
		return $this->intervalForRemindShareAssignment;
	}
	
	protected function getCollectionOptions(){
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions(){
		return self::$resourceOptions;
	}
}