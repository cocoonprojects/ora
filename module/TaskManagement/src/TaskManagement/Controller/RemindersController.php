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
		
		if (!isset($data['reminder']) || $data['reminder'] == ''){
			// HTTP STATUS CODE 400: Bad Request
			$this->response->setStatusCode(400);
			return $this->response;
		}

		switch ($data['reminder']) {
			case "assignment-of-shares":
				$timeboxForShareAssignment = $this->getServiceLocator()->get('Config')['share_assignment_timebox'];
				$tasksToNotify = $this->taskService->findAcceptedTasksBefore($timeboxForShareAssignment);

				if(is_array($tasksToNotify) && count($tasksToNotify) > 0){
						
					array_map(array($this, 'remindShareAssignmentOnTask'),  $tasksToNotify);
				}
				break;
			default:
				// HTTP STATUS CODE 405
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
					'emailSubject' => "O.R.A. - your contribution is required!",
					'url' => 'http://'.$_SERVER['SERVER_NAME'].'/task-management#'.$taskToNotify->getId()
			);
			
			$this->notificationService->sendEmailNotificationForShareAssignment($params);
		}
	}
	
	
	protected function getCollectionOptions(){
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions(){
		return self::$resourceOptions;
	}
}