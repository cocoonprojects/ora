<?php
namespace TaskManagement\Controller;

use Application\Controller\OrganizationAwareController;
use Application\IllegalStateException;
use Application\View\ErrorJsonModel;
use People\Service\OrganizationService;
use TaskManagement\Service\StreamService;
use TaskManagement\Service\TaskService;
use TaskManagement\Task;
use TaskManagement\View\TaskJsonModel;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\I18n\Validator\IsInt;
use Zend\Validator\EmailAddress;
use Zend\Validator\GreaterThan;
use Zend\Validator\InArray as StatusValidator;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex as UuidValidator;
use Zend\Validator\ValidatorChain;

class TasksController extends OrganizationAwareController
{
	const DEFAULT_TASKS_LIMIT = 10;
	protected static $collectionOptions = ['GET', 'POST'];
	protected static $resourceOptions = ['DELETE', 'GET', 'PUT'];

	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var StreamService
	 */
	private $streamService;
	/**
	 * @var integer
	 */
	protected $listLimit = self::DEFAULT_TASKS_LIMIT;

	public function __construct(TaskService $taskService, StreamService $streamService, OrganizationService $organizationService)
	{
		parent::__construct($organizationService);
		$this->taskService = $taskService;
		$this->streamService = $streamService;
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
		$view = new TaskJsonModel($this);
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

		if(!$this->isAllowed($this->identity(), $this->organization, 'TaskManagement.Task.list')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$decisions = $this->getRequest()->getQuery("decisions");
		if (empty($decisions)) {
			$decisions = 'false';
		}
		$filters["decisions"] = $decisions;

		$integerValidator = new ValidatorChain();
		$integerValidator
			->attach(new IsInt())
			->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));
		$offset = $this->getRequest()->getQuery("offset");
		$offset = $integerValidator->isValid($offset) ? intval($offset) : 0;
		$limit = $this->getRequest()->getQuery("limit");
		$limit = $integerValidator->isValid($limit) ? intval($limit) : $this->getListLimit();

		$filters["startOn"] = $this->getDateTimeParam("startOn");
		$filters["endOn"]   = $this->getDateTimeParam("endOn");

		$sorting = [];
		$orderBy = $this->getRequest()->getQuery("orderBy");
		if (!empty($orderBy) && in_array($orderBy, ['mostRecentEditAt'])) {
			$sorting['orderBy'] = $orderBy;
		}

		$orderType = $this->getRequest()->getQuery("orderType");
		if (!empty($orderBy) && !empty($orderType) && in_array(strtolower($orderType), ['asc','desc'])) {
			$sorting['orderType'] = $orderType;
		}

		$emailValidator = new EmailAddress(['useDomainCheck' => false]);
		$memberEmail = $this->getRequest()->getQuery("memberEmail");
		if($memberEmail && $emailValidator->isValid($memberEmail)) {
			$filters["memberEmail"] = $memberEmail;
		}

		$uuidValidator = new UuidValidator(['pattern' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})']);
		$memberId = $this->getRequest()->getQuery("memberId");
		if($memberId && $uuidValidator->isValid($memberId)) {
			$filters["memberId"] = $memberId;
		}

		$streamId = $this->getRequest()->getQuery('streamId');
		if($streamId && $uuidValidator->isValid($streamId)) {
			$filters["streamId"] = $streamId;
		}

		$statusValidator = new StatusValidator([
			'haystack' => [
				Task::STATUS_IDEA,
				Task::STATUS_OPEN,
				Task::STATUS_ONGOING,
				Task::STATUS_COMPLETED,
				Task::STATUS_ACCEPTED,
				Task::STATUS_CLOSED
			]
		]);
		$status = $this->getRequest()->getQuery('status');
		if(!(is_null($status) || $status == '')) {
			if($statusValidator->isValid($status)) {
				$filters["status"] = $status;
			} else {
				$view = new TaskJsonModel($this, $this->organization);
				$view->setVariables(['resource' => [], 'totalTasks' => 0]);
				return $view;
			}
		}

		$availableTasks = $this->taskService->findTasks($this->organization, $offset, $limit, $filters, $sorting);

		$view = new TaskJsonModel($this, $this->organization);
		$totalTasks = $this->taskService->countOrganizationTasks($this->organization, $filters);
		$view->setVariables(['resource'=>$availableTasks, 'totalTasks'=>$totalTasks]);

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
		if(!$this->isAllowed($this->identity(), null, 'TaskManagement.Task.create')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$error = new ErrorJsonModel();
		$validator = new NotEmpty();
		if (!isset($data['streamID'])){
			$error->addSecondaryErrors('stream', ['Stream id cannot be empty']);
		}
		if (!isset($data['subject'])){
			$error->addSecondaryErrors('subject', ['Subject cannot be empty']);
		}else{
			$subjectFilters = new FilterChain();
			$subjectFilters->attach(new StringTrim())
				->attach(new StripNewlines())
				->attach(new StripTags());
			$subject = $subjectFilters->filter($data['subject']);
			if (!$validator->isValid($subject)){
				$error->addSecondaryErrors('subject', ['Subject cannot be accepted']);
			}
		}
		if (!isset($data['description'])){
			$error->addSecondaryErrors('description', ['Description cannot be empty']);
		}else{
			$descriptionFilter = new FilterChain();
			$descriptionFilter->attach(new StringTrim())
				->attach(new StripTags());
			$description = $descriptionFilter->filter($data['description']);
			if (!$validator->isValid($description)){
				$error->addSecondaryErrors('description', ['Description cannot be accepted']);
			}
		}

		if (isset($data['decision'])){
			$decision = $data['decision'];

			if (!in_array($decision, ['true', 'false'])){
				$error->addSecondaryErrors('decision', ['Decision cannot be accepted']);
			}
		}

		if($error->hasErrors()){
			$error->setCode(400);
			$error->setDescription('Specified values are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}
		$stream = $this->streamService->getStream($data['streamID']);
		if(is_null($stream)) {
			// Stream Not Found
			$this->response->setStatusCode(404);
			return $this->response;
		}

		$this->transaction()->begin();

		$options = null;

		if (isset($data['decision'])) {
			$options = ['decision' => $data['decision']];
		}

		try {
			$task = Task::create(
				$stream,
				$subject,
				$this->identity(),
				$options
			);
			$task->setDescription($description, $this->identity());
			$task->addMember($this->identity(), Task::ROLE_OWNER);
			$this->taskService->addTask($task);
			$this->transaction()->commit();

			$url = $this->url()->fromRoute('tasks', array('orgId' => $task->getOrganizationId(), 'id' => $task->getId()));
			$this->response->getHeaders()->addHeaderLine('Location', $url);
			$this->response->setStatusCode(201);
			$view = new TaskJsonModel($this);
			$view->setVariable('resource', $task);
			return $view;
		} catch (\Exception $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(500);
		}
		return $this->response;
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
		$error = new ErrorJsonModel();
		$validator = new NotEmpty();
		if (!isset($data['subject'])) {
			$error->addSecondaryErrors('subject', ['Subject cannot be empty']);
		}else{
			$subjectFilters = new FilterChain();
			$subjectFilters->attach(new StringTrim())
				->attach(new StripNewlines())
				->attach(new StripTags());
			$subject = $subjectFilters->filter($data['subject']);
			if (!$validator->isValid($subject)){
				$error->addSecondaryErrors('subject', ['Subject cannot be accepted']);
			}
		}

		if (!isset($data['description'])) {
			$error->addSecondaryErrors('description', ['Description cannot be empty']);
		}else{
			$descriptionFilter = new FilterChain();
			$descriptionFilter->attach(new StringTrim())
				->attach(new StripTags());
			$description = $descriptionFilter->filter($data['description']);
			if (!$validator->isValid($description)){
				$error->addSecondaryErrors('description', ['Description cannot be accepted']);
			}
		}

		if($error->hasErrors()){
			$error->setCode(400);
			$error->setDescription('Specified values are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}

		$task = $this->taskService->getTask($id);
		if(is_null($task)) {
			$this->response->setStatusCode(404);
			return $this->response;
		}
		if(!$this->isAllowed($this->identity(), $task, 'TaskManagement.Task.edit')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$this->transaction()->begin();
		try {
			$task->setSubject($data['subject'], $this->identity());
			$task->setDescription($data['description'], $this->identity());
			$this->transaction()->commit();
			// HTTP STATUS CODE 202: Element Accepted
			$this->response->setStatusCode(202);
			$view = new TaskJsonModel($this);
			$view->setVariable('resource', $task);
			return $view;
		} catch (\Exception $e) {
			$this->response->setStatusCode(500);
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
		if(!$this->isAllowed($this->identity(), $task, 'TaskManagement.Task.delete')) {
			$this->response->setStatusCode(403);
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

	public function setListLimit($size){
		if(is_int($size)){
			$this->listLimit = $size;
		}
	}

	public function getListLimit(){
		return $this->listLimit;
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
