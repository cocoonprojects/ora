<?php

namespace Kanbanize\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Ora\Kanbanize;
use Ora\TaskManagement\Task;
use Ora\Kanbanize\KanbanizeTask;
use Ora\Kanbanize\KanbanizeAPI;

/**
 * Service Kanbanize
 *
 * @author Andrea Lupia <alupia@dimes.unical.it>
 *
 */
class KanbanizeService implements EventManagerAwareInterface 
{

  /**
   * Event Manager
   * 
   * @var \Zend\EventManager\EventManagerInterface
   */
  private $eventManager;

  /**
   * Kanbanize API
   *
   * @var \Ora\KanbanizeAPI\KanbanizeAPI
   */
  private $kanbanize;
  /**
   * 
   *
   */
  private $entityManager; 
  
  /*
   * Constructs service 
   * 
   */
  public function __construct() 
  {
	$this->kanbanize = new \Ora\Kanbanize\KanbanizeAPI();
  }

  /**
   * @param string $key
   */
  public function setApiKey($key) {
  	$this->kanbanize->setApiKey($key);
  }
  
  /**
   * @param string $url
   */
  public function setUrl($url) {
  	$this->kanbanize->setUrl($url);
  }
  
  /**
   * @param KanbanizeTask $kanbanizeTask
   */
  public function acceptTask($kanbanizeTask) {
  	$boardId = $kanbanizeTask->getBoardId();
  	$taskId = $kanbanizeTask->getTaskId();
  	$task = $this->kanbanize->getTaskDetails($boardId, $taskId);
  	if(isset($task['Error'])) {
  		//return 0;
  		return $task['Error'];
  	}
  	if($task['columnname'] == 'Backlog') {
  		
  		//Task can be accepted
  		return $this->kanbanize->moveTask($boardId, $taskId, 'Requested');
  	}
  	else {
  		//Task is in a column different than Backlog, so it was already accepted
  		return 0;
  	}
  }
  
  /**
   * @param KanbanizeTask $kanbanizeTask
   */
  
  public function createTask($kanbanizeTask){
  		
  	$boardId = $kanbanizeTask->getBoardId();
  	$kanbanizeTitle->getKanbanizeTitle();
  	$options = array();
  	$options["title"]=$kanbanizeTitle;
  	 return $this->kanbanize-> createNewTask($boardid, $options);
  	
  }
  
  
  
  
  
  /**
   * @param 
   */
  
  public function getTasksinBacklog($boardId){
 	$tasks_to_return = array();
  	$tasks = $this->kanbanize->getAllTasks($boardId);
  	foreach ($tasks as $singletask ){
  		if ($singletask["columnname"]=="Backlog")
  			$tasks_to_return[]=$singletask;
  	}
  	
  	return $tasks_to_return;
  	
  }
  /**
   * Injects Event Manager (ZF2 component) into this class
   *
   * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
   */
  public function setEventManager(EventManagerInterface $events)
  {
       $events->setIdentifiers(array(
           __CLASS__,
           get_called_class(),
       ));
       $this->eventManager = $events;
       return $this;
   }

   /**
    * Fetches Event Manager (ZF2 component) from this class
    *
    * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
    */
   public function getEventManager()
   {
       if (null === $this->eventManager) {
           $this->setEventManager(new EventManager());
       }
       return $this->eventManager;
   }
   
   public function setEntityManager($entityManager){
   	
   	$this->entityManager= $entityManager;
   	
   }
   
   public function getEntityManger(){
   	return $this->entityManager;
   }

}