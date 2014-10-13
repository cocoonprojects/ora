<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Kanbanize for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Kanbanize\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\Kanbanize\KanbanizeTask;
use Zend\View\Model\ViewModel;
use Kanbanize\Service\KanbanizeService;
use Zend\Db\Sql\Predicate\IsNull;

class KanbanizeController extends AbstractHATEOASRestfulController
{
	protected static $resourceOptions = array ('GET','POST','PUT');
	protected static $collectionOptions = array ('DELETE','GET');
	
	/**
	 * @var KanbanizeService
	 */
	protected $kanbanizeService;
	
	
	/**
	 * 
	 * @param unknown $data
	 */
	public function create($data){
		//kanbanize api take 
		$validator_NotEmpty = new \Zend\Validator\NotEmpty();
		$validator_Alnum =  new Zend\Validator\Alnum();
	
		
		if(!isset($data["boardid"])){
			//bad request
			$this->response->setStatusCode(400);
			return $this->response;
		}
		
		$boardid = $data["boardid"];
		if(! $validator_NotEmpty->isValid($boardid) || !$validator_Alnum->isValid($boardid) ){
			// request not correct
			$this->response->setStatusCode(406);
			return $this->response;
			
		}
		
		$kanbanizeTask = new KanbanizeTask(100, new \DateTime());
		$kanbanizeTask->setKanbanizeTitle("titolo prova rest");
		$result = $this->getKanbanizeService()->createTask($kanbanizeTask);
		
		if (is_null(result) ){
			$this->response->setStatusCode(400);
			return $this->response;
		}else{
			$this->response->setStatusCode(201);
			return $this->response;
		}
		//TODO $this->getKanbanizeService()->
		
		
	}
	
	public function update($id,$data){

		if(!isset($data['boardid'])){
			//bad request
			$this->response->setStatusCode(400);
			return $this->response;
		}
		
		if(!isset($id)){
			//bad request
			$this->response->setStatusCode(400);
			return $this->response;
		}
		
		//  TODO insert validators with 
		
		// mock task
		$kanbanizeTask = new KanbanizeTask($id, new \DateTime());

		$kanbanizeTask->setBoardId($data["boardid"]);
		$kanbanizeTask->setTaskId($id);
		if($kanbanizeTask->getTask()->isAcceptable()){
			$result = $this->getKanbanizeService()->acceptTask($kanbanizeTask);
			}else{
				$this->response->setStatusCode(400);
				return $this->response;
			}
		if ($result==1){
			$this->response->setStatusCode(200);
			return $this->response;
		}else{
			$this->response->setStatusCode(400);
			return $this->response;
		}
		
		
	}
	
//     public function indexAction()
//     {
//     	$this->kanbanizeService = $this->getServiceLocator()->get('Kanbanize\Service\Kanbanize');
//     	$tasks = $this->kanbanizeService->getTasksinBacklog(3);
//         return new ViewModel(array(
//     			'tasks' => $tasks
//     		)
//     	);
//     }

//     public function fooAction()
//     {
//         // This shows the :controller and :action parameters in default route
//         // are working when you browse to /kanbanize/kanbanize/foo
//         return array();
//     }
    
//     public function testAction() {
//         $this->kanbanizeService = $this->getServiceLocator()->get('Kanbanize\Service\Kanbanize');
//     	$task = new KanbanizeTask(9, new \DateTime());
//     	$task->setBoardId(3);
//     	$task->setTaskId(9);
//     	return new ViewModel(array(
//     			'response' => $this->kanbanizeService->acceptTask($task)
//     		)
//     	);
//     }
    
     protected function getKanbanizeService(){
     	//singleton
     	if (!isset($this->kanbanizeService))
     	$this->kanbanizeService = $this->getServiceLocator()->get('Kanbanize\Service\Kanbanize');
    	
     	return $this->kanbanizeService;
    	
     }

	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
	
	
}
