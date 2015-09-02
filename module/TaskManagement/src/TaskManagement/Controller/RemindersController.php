<?php 

namespace TaskManagement\Controller;

use ZFX\Rest\Controller\HATEOASRestfulController;
use TaskManagement\Service\NotifyMailListener;
use TaskManagement\Service\TaskService;
use TaskManagement\Entity\Task;
use TaskManagement\Entity\TaskMember;
use Zend\View\Model\ViewModel;




use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;

class RemindersController extends HATEOASRestfulController
{

	protected static $collectionOptions = [];
	protected static $resourceOptions = ['POST'];
	
	/**
	 *
	 * @var NotifyMailListener
	 */
	protected $notifyMailListener;
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
	

 	public function __construct(NotifyMailListener $notifyMailListener, TaskService $taskService) {
 		
 		$this->notifyMailListener = $notifyMailListener;
 		$this->taskService = $taskService;
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
	public function invoke($id, $data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), NULL, 'TaskManagement.Reminder.createReminder')){
			$this->response->setStatusCode(403);
			return $this->response;
		}
		
		switch ($id) {
			case "assignment-of-shares":
				
				$tasksToNotify = $this->taskService->findAcceptedTasksBefore($this->getIntervalForRemindAssignmentOfShares());
				
				if(is_array($tasksToNotify) && count($tasksToNotify) > 0){
					foreach ($tasksToNotify as $taskToNotify){
						$this->notifyMailListener->remindAssignmentOfShares($taskToNotify);
					}
				}
				break;
			default:
				$this->response->setStatusCode(405);
				break;
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
		return new \DateInterval('P6D');
	}
}
