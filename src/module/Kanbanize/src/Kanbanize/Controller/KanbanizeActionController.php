<?php

namespace Kanbanize\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Client;
use Ora\Kanbanize\KanbanizeTask;
use Ora\TaskManagement\Task;
use Application\Service\KanbanizeService;
use Rhumsaa\Uuid\Uuid;

/**
 * KanbanizeActionController
 *
 * @author
 *
 * @version
 *
 */
class KanbanizeActionController extends AbstractActionController {
	
	/**
	 * @var KanbanizeService
	 */
	private $kanbanizeService;

	
	public function indexAction() {
		
 		$method = $this->params()->fromQuery('method', 'get');
		$id = $this->getEvent()->getRouteMatch()->getParam('id');
		$ch = curl_init('http://160.97.24.61/staging/task-management/tasks/'.$id.'/transitions');
		switch($method) {
			case 'accept':
				// only for test purposes the id of the board is hardcoded
				// this is a test controller
				$data = array("action"=>"accept");
				curl_setopt($ch, CURLOPT_POST, true);
				//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
				
				$response = curl_exec($ch);
				break;
			case 'ongoing':
				$data = array("action"=>"ongoing");
				curl_setopt($ch, CURLOPT_POST, true);
				//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
				$response = curl_exec($ch);
				break;
		}

		$fm = $this->flashMessenger();
		
		if(!$response || curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) {
			$fm->addErrorMessage("Cannot move task ".curl_getinfo($ch,CURLINFO_HTTP_CODE)." ");
		}
		else {
			$fm->addSuccessMessage("Task moved successfully ".curl_getinfo($ch,CURLINFO_HTTP_CODE));
		}
		
		$this->redirect()->toUrl('http://160.97.24.61/staging/kanbanize/list');
		
	}
	
	protected function getKanbanizeService(){
		//singleton
		if (!isset($this->kanbanizeService))
			$this->kanbanizeService = $this->getServiceLocator()->get('TaskManagement\Service\Kanbanize');
		return $this->kanbanizeService;
		 
	}
	
	public function listAction(){
		// here retrieve the task to show in the page
		// put in the view with key tasks
		
		$tasks = $this->getKanbanizeService()->listAvailableKanbanizeTasks();
		
		$taskList = array();
		
		$acceptable = array();
		
		$back2ongoing = array();

		foreach ($tasks as $task) {
			switch($task->getStatus()) {
				//FIXME backlog
				case -1:
					break;
				case Task::STATUS_IDEA:
					break;
				case Task::STATUS_COMPLETED:
					$acceptable[] = $task->getId();
				case Task::STATUS_OPEN:
					$back2ongoing[] = $task->getId();
					break;
				case Task::STATUS_ONGOING:
					break;
				case Task::STATUS_ACCEPTED:
					break;
			}
		}
		
		$view = new ViewModel(array('tasks' => $tasks, 'acceptable' => $acceptable, 'back2ongoing' => $back2ongoing));
		
		return $view;
		
	}

}