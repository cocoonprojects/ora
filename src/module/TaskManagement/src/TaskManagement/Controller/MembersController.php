<?php

namespace TaskManagement\Controller;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;
use Zend\Stdlib\InitializableInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class MembersController extends AbstractHATEOASRestfulController implements InitializableInterface
{
    protected static $collectionOptions = array('DELETE', 'POST');
    protected static $resourceOptions = array('DELETE', 'POST');
	protected $taskService;
    protected $userService;
    
    public function init()
    {
        // Executed before any other method
    }
    
    public function get($id)
    {        
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
        return $this->response;
    }
	
    public function getList()
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
        return $this->response;
    }
    
    /**
     * Create a new task into specified project
     * @method POST
     * @link http://oraproject/task-management/tasks/[:taskid]/join-members/
     * @param string $taskid ID of the specified task when add new member
     * @param string $id ID of user to add into the team of specified task
     * @return HTTPStatusCode
     * @author Giannotti Fabio
     */
    //
    public function create($data)
    {
        // Definition of used Zend Validators
        $validator_NotEmpty = new \Zend\Validator\NotEmpty();

        $taskid = $this->params('taskid');
        $id = $this->params('id');
        
        // Check if Task ID is empty or null
        if (!$validator_NotEmpty->isValid($taskid))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
        
            return $this->response;
        }
        
        // Check if Member ID is empty or null
        if (!$validator_NotEmpty->isValid($id))
        {
            // HTTP STATUS CODE 406: Not Acceptable
            $this->response->setStatusCode(406);
        
            return $this->response;
        }
        
        // Check if Task with specified ID exist
        $task = $this->getTaskService()->findTask($taskid);
        if (!($task instanceof \Ora\TaskManagement\Task))
        {
            // HTTP STATUS CODE 404: Not Found
            $this->response->setStatusCode(404);
             
            return $this->response;
        }
        
        // Check if User with specified ID exist
        $user = $this->getUserService()->findUser($id);
        if (!($user instanceof \Ora\User\User))
        {
            // HTTP STATUS CODE 404: Not Found
            $this->response->setStatusCode(404);
             
            return $this->response;
        }
        
        // TODO: Aggiungere controllo per verificare che l'utente che sta eseguendo la richiesta
        // corrisponda effettivamente all'utente attualmente loggato oppure no? In caso contrario
        // un utente potrebbe far joinare qualsiasi altro utente in qualsiasi task esistente...
         
        // Adding USER (member) into members of specified TASK
       	$this->getTaskService()->addTaskUser($task, $user);
        
        // HTTP STATUS CODE 201: Created
    	$this->response->setStatusCode(201);
    	
    	return $this->response;
    }

    public function update($id, $data)
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
        return $this->response;
    }
    
    public function replaceList($data)
    {
        // HTTP STATUS CODE 405: Method not allowed
        $this->response->setStatusCode(405);
         
        return $this->response;
    }
    
    public function delete($id)
    {
        // HTTP STATUS CODE 405: Method not allowed
        //$this->response->setStatusCode(405);
         
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
    
    protected function getJsonModelClass()
    {
        return $this->jsonModelClass;
    }
    
    protected function getUserService()
    {
        if (!isset($this->userService))
        {
            $serviceLocator = $this->getServiceLocator();
            $this->userService = $serviceLocator->get('User\UserService');
        }
    
        return $this->userService;
    }
    
    protected function getTaskService() 
    {
        if (!isset($this->taskService)) 
        {
            $serviceLocator = $this->getServiceLocator();
            $this->taskService = $serviceLocator->get('TaskManagement\TaskService');
        }
        
        return $this->taskService;
    }
}