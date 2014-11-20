<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationService;
use Ora\DuplicatedDomainEntityException;
use Ora\TaskManagement\TaskService;
use Ora\TaskManagement\Task;
use Ora\IllegalStateException;
use Ora\DomainEntityUnavailableException;
use Zend\Authentication\AuthenticationServiceInterface;
use Ora\ProjectManagement\ProjectService;

class MembersController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array('DELETE', 'POST');
    protected static $resourceOptions = array();

    /**
     * 
     * @var TaskService
     */
    protected $taskService;
    
    /**
     * 
     * @var AuthenticationService
     */
    protected $authService;
    
    /**
     *
     * @var ProjectService
     */
    protected $projectService;  
      
    /**
     * 
     * @var Task
     */
    protected $task = null;
    
    public function __construct(TaskService $taskService, AuthenticationServiceInterface $authService, ProjectService $projectService) {
    	$this->authService = $authService;
		$this->taskService = $taskService;	
		$this->projectService = $projectService;
    }
    
    public function preDispatch($e)
    {
        if (null !== $this->params()->fromRoute('taskId'))
        {            
            $id = $this->params()->fromRoute('taskId');
            $this->task = $this->taskService->getTask($id);
            if (is_null($this->task))
            {
                $this->response->setStatusCode(404);
                return $this->response;
            }
        }
    }
    
    /**
     * @author Giannotti Fabio
     */
    public function create($data)
    {  	    	
    	try {
    		
    		$loggedUser = $this->authService->getIdentity()['user'];
    		 
    		$projectId = $this->task->getProjectId();

    		$project = $this->projectService->getProject($projectId);
    		
    		if(is_null($project)) {
    			// Project Not Found   
    			echo "Progetto not found"; 			
    			$this->response->setStatusCode(404);
    			return $this->response;
    		}    		
    		    		
       		$this->task->addMember($loggedUser, $loggedUser);
       		$this->taskService->editTask($this->task);
	    	$this->response->setStatusCode(201);
    	} catch (DuplicatedDomainEntityException $e) {    		
    		$this->response->setStatusCode(204);
        } catch (IllegalStateException $e) {
        	$this->response->setStatusCode(406);	// Not acceptable
    	}
    	
    	return $this->response;
    }

    public function deleteList()
    {
        try {
        	

        	$loggedUser = $this->authService->getIdentity()['user'];
        	         	
        	$projectId = $this->task->getProjectId();
        	
        	$project = $this->projectService->getProject($projectId);
        	
        	if(is_null($project)) {
        		// Project Not Found
        		echo "Progetto not found";
        		$this->response->setStatusCode(404);
        		return $this->response;
        	}
        	      	
       		$this->task->removeMember($loggedUser, $loggedUser);
       		$this->taskService->editTask($this->task);
	    	$this->response->setStatusCode(200);
        } catch (DomainEntityUnavailableException $e) {
        	$this->response->setStatusCode(204);	// No content
        } catch (IllegalStateException $e) {
        	$this->response->setStatusCode(406);	// Not acceptable
        }
    	return $this->response;
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