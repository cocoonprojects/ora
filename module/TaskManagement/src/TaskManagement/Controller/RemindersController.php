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
	protected $intervalForRemindAssignmentOfShares;
	/**
	 *
	 * @var Acl
	 */
	private $acl;
	
 	public function __construct(NotificationService $notificationService, TaskService $taskService, Acl $acl) {
 		
 		$this->notificationService = $notificationService;
 		$this->taskService = $taskService;
 		$this->acl = $acl;
 		$this->intervalForRemindAssignmentOfShares = self::getDefaultIntervalToRemindAssignmentOfShares(); 		
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
			$this->response->setStatusCode(403);
			return $this->response;
		}
		
		if (!isset($data['id']) || $data['id'] == ''){
			$this->response->setStatusCode(400);
			return $this->response;
		}

		switch ($data['id']) {
			case "assignment-of-shares":
				
				$tasksToNotify = $this->taskService->findAcceptedTasksBefore($this->getIntervalForRemindAssignmentOfShares());

				if(is_array($tasksToNotify) && count($tasksToNotify) > 0){
						
					array_map(array($this, 'remindAssignmentOfSharesOnSingleTask'),  $tasksToNotify);
				}
				break;
			default:
				$this->response->setStatusCode(405);
				break;
		}

		return $this->response;
	}
	
	
	private function remindAssignmentOfSharesOnSingleTask(Task $taskToNotify){
		
		$taskMembersWithEmptyShares = $taskToNotify->findMembersWithEmptyShares();		
		foreach ($taskMembersWithEmptyShares as $member){
			$this->notificationService->sendEmailNotificationForAssignmentOfShares($taskToNotify, $member);
		}
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
		return new \DateInterval('P6D');
	}
}