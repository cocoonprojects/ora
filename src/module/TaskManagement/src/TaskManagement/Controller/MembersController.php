<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\IllegalStateException;
use Ora\DuplicatedDomainEntityException;
use Ora\DomainEntityUnavailableException;
use Ora\TaskManagement\TaskService;


class MembersController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array();
    protected static $resourceOptions = array('DELETE', 'POST');

    /**
     * 
     * @var TaskService
     */
    protected $taskService;
        
    public function __construct(TaskService $taskService) {
		$this->taskService = $taskService;	
    }
    
    public function invoke($id, $data)
    {
    	$task = $this->taskService->getTask($id);
    	
        if (is_null($task)) {
        	$this->response->setStatusCode(404);
			return $this->response;
        }
    	
    	$loggedUser = $this->identity()['user'];
    	$this->transaction()->begin();
    	try {    		
       		$task->addMember($loggedUser, $loggedUser);
       		$this->transaction()->commit();
	    	$this->response->setStatusCode(201);
    	} catch (DuplicatedDomainEntityException $e) {    		
       		$this->transaction()->rollback();
    		$this->response->setStatusCode(204);
        } catch (IllegalStateException $e) {
       		$this->transaction()->rollback();
        	$this->response->setStatusCode(412);	// Preconditions failed
    	}
    	
    	return $this->response;
    }

    public function delete($id)
    {
        $task = $this->taskService->getTask($id);
        if (is_null($task)) {
        	$this->response->setStatusCode(404);
			return $this->response;
        }
        
       	$loggedUser = $this->identity()['user'];
    	$this->transaction()->begin();
       	try {
       		$task->removeMember($loggedUser, $loggedUser);
       		$this->transaction()->commit();
	    	$this->response->setStatusCode(200);
        } catch (DomainEntityUnavailableException $e) {
        	$this->transaction()->rollback();
        	$this->response->setStatusCode(204);	// No content
        } catch (IllegalStateException $e) {
       		$this->transaction()->rollback();
        	$this->response->setStatusCode(412);	// Preconditions failed
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