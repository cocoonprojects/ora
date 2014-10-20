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
		switch($method) {
			case 'update':
		
// create task and persist it only for test purposes
   $temptask = new Task(uniqid(),new \DateTime());
   $temptask->setSubject("soggetto di prova");
   $entity_manager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
   $entity_manager->persist($temptask);
   $entity_manager->flush();
  
   
				

				

				$data = array("boardid" => "3");
				$ch = curl_init('http://192.168.56.111/kanbanize/task/'.'8');
				
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
				
		}
		
		$view = new ViewModel(array('response' => $response,"curl"=>$ch));
		
		//$view ->setTemplate("kanbanize/kanbanize/");
		return $view;
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
		// put in the view wit key tasks
		
		$boardId = 3;
		$status = 'Backlog';
		
		$tasks = $this->getKanbanizeService()->getTasks($boardId, $status);
		
		$taskList = array();

		foreach ($tasks as $singletask) {
			$task = new KanbanizeTask(uniqid(), $boardId, $singletask['taskid'], new \DateTime());
			$task->setSubject($singletask['description']);
			$task->setStatus($singletask['position']);
			$taskList[] = $task;
		}
		
		$view = new ViewModel(array('tasks' => $taskList));
		
		return $view;
		
	}
	
}