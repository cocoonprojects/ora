<?php
namespace TaskManagement\Controller;

use Application\Controller\OrganizationAwareController;
use Application\IllegalStateException;
use People\Service\OrganizationService;
use TaskManagement\Service\StreamService;
use TaskManagement\Service\TaskService;
use TaskManagement\Task;
use TaskManagement\View\TaskJsonModel;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\Permissions\Acl\Acl;
use Zend\Validator\NotEmpty;

class TasksController extends OrganizationAwareController
{
	protected static $collectionOptions = ['GET', 'POST'];
	protected static $resourceOptions = ['DELETE', 'GET', 'PUT'];

	/**
	 * 
	 * @var TaskService
	 */
	private $taskService;
	/**
	 *
	 * @var StreamService
	 */
	private $streamService;
	/**
	 * @var Acl
	 */
	private $acl;
	/**
	 *@var \DateInterval
	 */
	protected $intervalForCloseTasks;
	
	public function __construct(TaskService $taskService, StreamService $streamService, Acl $acl, OrganizationService $organizationService)
	{
		parent::__construct($organizationService);
		$this->taskService = $taskService;
		$this->streamService = $streamService;
		$this->acl = $acl;
		$this->intervalForCloseTasks = new \DateInterval('P7D');
	}
	
	public function get($id)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$task = $this->taskService->findTask($id);
		if(is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $task, 'TaskManagement.Task.get')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$this->response->setStatusCode(200);
		$view = new TaskJsonModel($this->url(), $this->identity(), $this->acl, $this->organization);
		$view->setVariable('resource', $task);
		return $view;
	}
	
	/**
	 * Return a list of available tasks
	 * @method GET
	 * @link http://oraproject/task-management/tasks?streamID=[uuid]
	 * @return TaskJsonModel
	 */
	public function getList()
	{
		if (is_null($this->identity())){
			$this->response->setStatusCode(401);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $this->organization, 'TaskManagement.Task.list')){
			$this->response->setStatusCode(403);
			return $this->response;
		}
		
		$streamID = $this->getRequest()->getQuery('streamID');
		
		$availableTasks = is_null($streamID) ? $this->taskService->findTasks($this->organization) : $this->taskService->findStreamTasks($streamID);

		$view = new TaskJsonModel($this->url(), $this->identity(), $this->acl, $this->organization);
		$view->setVariable('resource', $availableTasks);
		
		return $view;
	}

	/**
	 * Create a new task into specified stream
	 * @method POST
	 * @link http://oraproject/task-management/tasks
	 * @param array $data['streamID'] Parent stream ID of the new task
	 * @param array $data['subject'] Task subject
	 * @return HTTPStatusCode
	 */
	public function create($data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		if (!isset($data['streamID']) || !isset($data['subject']))
		{
			// HTTP STATUS CODE 400: Bad Request
			$this->response->setStatusCode(400);
			return $this->response;
		}

		$stream = $this->streamService->getStream($data['streamID']);
		if(is_null($stream)) {
			// Stream Not Found
			$this->response->setStatusCode(404);
			return $this->response;
		}

		$filters = new FilterChain();
		$filters->attach(new StringTrim())
				->attach(new StripNewlines())
				->attach(new StripTags());
		$subject = $filters->filter($data['subject']);

		$validator = new NotEmpty();
		if (!$validator->isValid($subject))
		{
			$this->response->setStatusCode(406);
			return $this->response;
		}

		$this->transaction()->begin();
		try {
			$task = Task::create($stream, $subject, $this->identity());
			$task->addMember($this->identity(), Task::ROLE_OWNER);
			$this->taskService->addTask($task);
			$this->transaction()->commit();

			$url = $this->url()->fromRoute('tasks', array('orgId' => $this->organization->getId(), 'id' => $task->getId()));
			$this->response->getHeaders()->addHeaderLine('Location', $url);
			$this->response->setStatusCode(201);
			return $this->response;
		} catch (\Exception $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(500);
			return $this->response;
		}
	}

	/**
	 * Update existing task with new data
	 * @method PUT
	 * @link http://oraproject/task-management/tasks/[:ID]
	 * @param array $id ID of the Task to update
	 * @param array $data['subject'] Update Subject for the selected Task
	 * @return HTTPStatusCode
	 */
	public function update($id, $data)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		if (!isset($data['subject'])) {
			$this->response->setStatusCode(204);	// HTTP STATUS CODE 204: No Content (Nothing to update)
			return $this->response;
		}

		$task = $this->taskService->getTask($id);
		if(is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		// Definition of used Zend Validators
		$validator_NotEmpty = new NotEmpty();

		// ...if exist check if subject it's empty
		if (!$validator_NotEmpty->isValid($data['subject']))
		{
			// HTTP STATUS CODE 406: Not Acceptable
			$this->response->setStatusCode(406);
			return $this->response;
		}

		$this->transaction()->begin();
		try {
			$task->setSubject($data['subject'], $this->identity());
			$this->transaction()->commit();
			// HTTP STATUS CODE 202: Element Accepted
			$this->response->setStatusCode(202);
			$view = new TaskJsonModel($this->url(), $this->identity(), $this->acl, $this->organization);
			$view->setVariable('resource', $task);
			return $view;
		} catch (\Exception $e) {
			$this->transaction()->rollback();
		}

		return $this->response;
	}

	/**
	 * Delete single existing task with specified ID
	 * @method DELETE
	 * @link http://oraproject/task-management/tasks/[:ID]
	 * @return HTTPStatusCode
	 * @author Giannotti Fabio
	 */
	public function delete($id)
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$task = $this->taskService->getTask($id);
		if(is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}

		if($task->getStatus() == Task::STATUS_DELETED) {
			$this->response->setStatusCode(204);
			return $this->response;
		}

		$this->transaction()->begin();
		try {
			$task->delete($this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(200);
		} catch (IllegalStateException $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(412);	// Preconditions failed
		}
		return $this->response;
	}

	public function getTaskService()
	{
		return $this->taskService;
	}

	protected function getCollectionOptions()
	{
		return self::$collectionOptions;
	}

	protected function getResourceOptions()
	{
		return self::$resourceOptions;
	}
	
	public function setIntervalForCloseTasks($interval){
		$this->intervalForCloseTasks = $interval;
	}

	public function getIntervalForCloseTasks(){
		return $this->intervalForCloseTasks;
	}
}
