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
		//TODO inserire subject e project in $data
		//kanbanize api take 
		$validator_NotEmpty = new \Zend\Validator\NotEmpty();
		$validator_Alnum =  new Zend\Validator\Alnum();
	
		
		if(!isset($data["boardid"])){
			//bad request
			$this->response->setStatusCode(400);
			return $this->response;
		}
		
		$boardId = $data["boardid"];
		if(! $validator_NotEmpty->isValid($boardId) || !$validator_Alnum->isValid($boardId) ){
			// request not correct
			$this->response->setStatusCode(406);
			return $this->response;
			
		}
		
		$taskId = uniqid();

		$result = $this->getKanbanizeService()->createNewTask(1, "arharharharha", $boardId);
		
		
		if ($result == 1){
			$this->response->setStatusCode(400);
		}else{
			$this->response->setStatusCode(201);
		}
		return $this->response;
		
		
	}
	public function update($id, $data) {
		$messagetoshow;
		
		// actions -> accept | OnGoing
		if (! isset ( $data ['action'] )) {
			$this->response->setStatusCode ( 400 );
			return $this->response;
		}
		
		$action = $data ["action"];
		
		if (! isset ( $data ['boardid'] )) {
			// bad request
			$this->response->setStatusCode ( 400 );
			return $this->response;
		}
		
		if (! isset ( $id )) {
			// bad request
			$this->response->setStatusCode ( 400 );
			return $this->response;
		}
		
		// TODO insert validators with
		
		// mock task
		$taskId = uniqid ();
		$boardId = $data ["boardid"];
		
		$kanbanizeTask = new KanbanizeTask ( $taskId, $boardId, $id, new \DateTime () );
		switch ($action) {
			
			case "accept" :
				if ($this->getKanbanizeService()->isAcceptable($kanbanizeTask)) {
					$result = $this->getKanbanizeService ()->moveTask($kanbanizeTask, KanbanizeTask::COLUMN_ACCEPTED);
				} else {
					$this->response->setStatusCode ( 400 );
				}
				if ($result == 1) {
					$this->response->setStatusCode ( 200 );
				} else {
					$this->response->setStatusCode ( 400 );
				}
				break;
			case "ongoing" :
				$result = $this->getKanbanizeService ()->moveTask($kanbanizeTask, KanbanizeTask::COLUMN_ONGOING);
				if ($result == 1) {
					$this->response->setStatusCode ( 200 );
				} else {
					$this->response->setStatusCode ( 400 );
				}
				
				break;
				
			
		}
		
		return $this->response;
	}
	

    
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
