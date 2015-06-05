<?php 

namespace TaskManagement\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use TaskManagement\Service\NotificationService;
use TaskManagement\Service\TaskService;
use TaskManagement\Entity\Task;
use Zend\Permissions\Acl\Acl;
use TaskManagement;
use TaskManagement\Entity\TaskMember;

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
	/**
	 *
	 * @var Acl
	 */
	private $acl;
	
 	public function __construct(NotificationService $notificationService, TaskService $taskService, Acl $acl) {
 		$this->notificationService = $notificationService;
 		$this->taskService = $taskService;
 		$this->acl = $acl;
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
		
		if(!$this->acl->isAllowed($this->identity()['user'], NULL, 'TaskManagement.Reminder.createReminder')){
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
			$this->notificationService->sendEmailNotificationForAssignmentOfShares($taskToNotify, $member);
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