<?php

namespace TaskManagement\Controller;

use Application\DomainEntityUnavailableException;
use Application\DuplicatedDomainEntityException;
use Application\IllegalStateException;
use Application\View\ErrorJsonModel;
use ZFX\Rest\Controller\HATEOASRestfulController;
use TaskManagement\Service\TaskService;
use TaskManagement\TaskInterface;
use TaskManagement\View\TaskJsonModel;

class AttachmentsController extends HATEOASRestfulController {

	protected static $collectionOptions = ['GET'];
	protected static $resourceOptions = ['POST'];

	/**
	 * @var TaskService
	 */
	protected $taskService;

	public function __construct(TaskService $taskService){
		$this->taskService = $taskService;
	}

	public function invoke($id, $data) {


		if (is_null($this->identity())) {
			$this->response->setStatusCode(401);

			return $this->response;
		}

		$error = new ErrorJsonModel();
		if (!is_array($data) || !isset($data['attachments'])) {
			$error->setCode(400);
			$error->addSecondaryErrors('attachments', ['attachments must be a valid json']);
			$error->setDescription('attachments json is not valid');
			$this->response->setStatusCode(400);

			return $error;
		}

		$task = $this->taskService
					 ->getTask($id);

		if (is_null($task)) {
			$this->response->setStatusCode(404);

			return $this->response;
		}

		$this->transaction()->begin();

		$attachments = $data['attachments'];

		try {
			$task->setAttachments($this->identity(), $attachments);

			$this->transaction()->commit();
			$this->response->setStatusCode(200);
			$view = new TaskJsonModel($this);
			$view->setVariable('resource', $task);

			return $view;
		} catch ( DuplicatedDomainEntityException $e ) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(409);
		} catch ( DomainEntityUnavailableException $e ) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(403);
		} catch ( IllegalStateException $e ) {
			$this->transaction()->rollback();
			error_log(print_r($e, true));
			$this->response->setStatusCode(412);
		}

		return $this->response;
	}

	public function getTaskService(){
		return $this->taskService;
	}
	protected function getCollectionOptions(){
		return self::$collectionOptions;
	}

	protected function getResourceOptions(){
		return self::$resourceOptions;
	}
}
