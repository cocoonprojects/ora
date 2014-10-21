<?php

namespace Kanbanize\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Client;
use Ora\Kanbanize\KanbanizeTask;
use Ora\TaskManagement\Task;
use Application\Service\KanbanizeService;

/**
 * TestKanbanizeActionController
 *
 * @author
 *
 * @version
 *
 */
class TestKanbanizeActionController extends AbstractActionController {
	
	/**
	 * @var KanbanizeService
	 */
	private $kanbanizeService;
	 
	public function indexAction() {
		
		$client = new Client();
		$method = $this->params()->fromQuery('method', 'get');
		$client = $client->setAdapter('Zend\Http\Client\Adapter\Curl')->setUri('http://localhost/kanbanize/task');
		//prepare request
		
		$id = $this->getEvent()->getRouteMatch()->getParam('id');
		$ch = curl_init('http://192.168.56.111/kanbanize/task/'.$id);
		switch($method) {
			case 'update':
		
// create task and persist it only for test purposes
//    $temptask = new Task(uniqid(),new \DateTime());
//    $temptask->setSubject("soggetto di prova");
//    $entity_manager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
//    $entity_manager->persist($temptask);
//    $entity_manager->flush();
  
   
				

				
				// only for test purposes the id of the board is hardcoded
				// this is a test controller
				
				$data = array("boardid" => "3","action"=>"accept");
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
				
				$response = curl_exec($ch);
// 				if(!$response) {
// 					return false;
// 				}
				break;
				
			case 'ongoing':
				$data = array("boardid" => "3","action"=>"ongoing");
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
				
				$response = curl_exec($ch);
				break;
		}

		$fm = $this->flashMessenger();
		
		if(!$response || curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) {
			$fm->addErrorMessage("Cannot move task");
		}
		else {
			$fm->addSuccessMessage("Task moved successfully");
		}
		
		$this->redirect()->toRoute('list', array('response' => $response));
		
	}
	
	protected function getKanbanizeService(){
		//singleton
		if (!isset($this->kanbanizeService))
			$this->kanbanizeService = $this->getServiceLocator()->get('Kanbanize\Service\Kanbanize');
		 
		return $this->kanbanizeService;
		 
	}
	
	public function listAction(){
		$entity_manager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
		// here retrieve the task to show in the page
		// put in the view with key tasks
		
		//TODO get dinamically board id
		$boardId = 3;
		$tasks = $this->getKanbanizeService()->getTasks($boardId);
		
		$taskList = array();
		
		$acceptable = array();
		
		$back2ongoing = array();

		foreach ($tasks as $singletask) {
			//TODO inserire utente reale e prendere id reale
			$task = new KanbanizeTask(uniqid(), $boardId, $singletask['taskid'], new \DateTime(), "Utente");
			$task->setSubject($singletask['description']);
			$task->setStatus(KanbanizeTask::getMappedStatus($singletask['columnname']));
			$task->setBoardId($boardId);
			$taskList[] = $task;
			switch($task->getStatus()) {
				case Task::STATUS_IDEA:
				case Task::STATUS_OPEN:
				case Task::STATUS_ONGOING:
					$acceptable[] = $task->getId();
					break;
				case Task::STATUS_COMPLETED:
					$acceptable[] = $task->getId();
				case Task::STATUS_COMPLETED:
					$back2ongoing[] = $task->getId();
					break;
			}
		}
		
		$view = new ViewModel(array('tasks' => $taskList, 'acceptable' => $acceptable, 'back2ongoing' => $back2ongoing));
		
		return $view;
		
	}
	
}