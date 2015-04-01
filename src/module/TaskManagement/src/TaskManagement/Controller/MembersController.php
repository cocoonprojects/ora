<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Ora\IllegalStateException;
use Ora\DuplicatedDomainEntityException;
use Ora\DomainEntityUnavailableException;
use Ora\TaskManagement\TaskService;
use Accounting\Service\AccountService;
use Ora\User\User;
use Ora\TaskManagement\Task;


class MembersController extends AbstractHATEOASRestfulController
{
    protected static $collectionOptions = array();
    protected static $resourceOptions = array('DELETE', 'POST');

    /**
     * 
     * @var TaskService
     */
    protected $taskService;
    /**
     * 
     * @var AccountService
     */
    protected $accountService;
        
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
    	
    	$identity = $this->identity()['user'];
    	$accountId = $this->getAccountId($identity);
    	$this->transaction()->begin();
    	try {    		
       		$task->addMember($identity, Task::ROLE_MEMBER, $accountId);
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
        
       	$identity = $this->identity()['user'];
    	$this->transaction()->begin();
       	try {
       		$task->removeMember($identity, $identity);
       		$this->transaction()->commit();
	    	$this->response->setStatusCode(200);
        } catch (DomainEntityUnavailableException $e) {
        	$this->transaction()->rollback();
        	$this->response->setStatusCode(204);	// No content = nothing changed
        } catch (IllegalStateException $e) {
       		$this->transaction()->rollback();
        	$this->response->setStatusCode(412);	// Preconditions failed
        }
    	return $this->response;
    }
    
    public function setAccountService(AccountService $accountService) {
    	$this->accountService = $accountService;
    }
    
    public function getAccountService() {
    	return $this->accountService;
    }
    
    protected function getCollectionOptions()
    {
        return self::$collectionOptions;
    }
    
    protected function getResourceOptions()
    {
        return self::$resourceOptions;
    }
    
    protected function getAccountId(User $user) {
    	if(is_null($this->accountService)){
    		return null;
    	}
    	$account = $this->accountService->findPersonalAccount($user);
    	if(is_null($account)) {
    		return null;
    	}
    	return $account->getId();
    }
}