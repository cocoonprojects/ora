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
use Zend\I18n\Validator\IsFloat;
use Zend\I18n\Validator\IsInt;
use Zend\Validator\GreaterThan;
use Zend\Validator\NotEmpty;
use Zend\Validator\ValidatorChain;


class AcceptancesController extends HATEOASRestfulController {

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
		if (is_null ( $this->identity () )) {
			// unauthorized
			$this->response->setStatusCode ( 401 );
			return $this->response;
		}
		$error = new ErrorJsonModel ();
		if (! isset ( $data ['value'] )) {
			$error->setCode ( 400 );
			$error->addSecondaryErrors ( 'value', [
					'Vote cannot be empty'
			] );
			$error->setDescription ( 'Specified vote value are not valid' );
			$this->response->setStatusCode ( 400 );
			return $error;
		}

		$vote = $data ['value'];
		$description = isset($data ['description']) ? $data['description'] : "";

		$validator = new ValidatorChain ();
		$validator->attach ( new NotEmpty (), true )->attach ( new IsInt (), true )->attach ( new GreaterThan ( [
				'min' => 0,
				'inclusive' => true
		] ), true );
		if (! ($validator->isValid ( $vote ))) {
			$error->setCode ( 400 );
			$error->addSecondaryErrors ( 'vote', $validator->getMessages () );
			$error->setDescription ( 'Specified vote value are not valid' );
			$this->response->setStatusCode ( 400 );
			return $error;
		}



		$task = $this->taskService->getTask ( $id );
		if (is_null( $task )) {
			// RESOURCE NOT FOUND
			$this->response->setStatusCode ( 404 );

			return $this->response;
		}

		if (! $this->isAllowed( $this->identity(), $this->taskService->findTask( $id ), 'TaskManagement.Task.accept' )) {
			$this->response->setStatusCode ( 403 );
			return $this->response;
		}

		$this->transaction()->begin();

		try {
			$task->addAcceptance( $vote, $this->identity(), $description );
			$this->transaction ()->commit ();
			$this->response->setStatusCode ( 201 );
			$view = new TaskJsonModel ( $this );
			$view->setVariable ( 'resource', $task );
			return $view;
		} catch ( DuplicatedDomainEntityException $e ) {
			$this->transaction ()->rollback ();
			$this->response->setStatusCode ( 409 ); // Conflict for duplicate entity
		} catch ( DomainEntityUnavailableException $e ) {
			$this->transaction ()->rollback ();
			$this->response->setStatusCode ( 403 ); // Forbidden because not a member
		} catch ( IllegalStateException $e ) {
			$this->transaction ()->rollback ();
//			error_log ( print_r ( $e, true ) );
			$this->response->setStatusCode ( 412 ); // Preconditions failed
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
