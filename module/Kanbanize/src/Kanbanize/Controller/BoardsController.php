<?php

namespace Kanbanize\Controller;

use Application\Controller\OrganizationAwareController;
use Application\View\ErrorJsonModel;
use People\Service\OrganizationService;
use People\Organization;
use Kanbanize\Service\ImportDirector;
use Zend\View\Model\JsonModel;
use Zend\Validator\InArray as StatusValidator;
use TaskManagement\Task;
use Zend\Escaper\Escaper;

class BoardsController extends OrganizationAwareController{

	protected static $resourceOptions = ['POST', 'GET'];
	protected static $collectionOptions= [];
	/**
	 * @var ImportDirector
	 */
	private $importer;

	public function __construct(OrganizationService $organizationService, ImportDirector $importer){
		parent::__construct($organizationService);
		$this->importer = $importer;
	}

	public function invoke($id, $data){
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		if(!$this->isAllowed($this->identity(), $this->organization, 'Kanbanize.BoardSettings.create')) {
			$this->response->setStatusCode(403);
			return $this->response;
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
		$error = new ErrorJsonModel();
		$columnMapping = [];
		foreach($data as $column=>$status){
			if(!$statusValidator->isValid($status)){
				$error->addSecondaryErrors($column, ["Invalid status: {$status}"]);
			}
			$columnMapping[urldecode($column)] = $status;
		}
		if($error->hasErrors()){
			$error->setCode(400);
			$error->setDescription('Some mappings are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}
		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$kanbanizeSettings = $organization->getSetting(Organization::KANBANIZE_KEY_SETTING);
		$kanbanizeSettings['boards'][$id]['columnMapping'] = $columnMapping;
		$this->transaction()->begin();
		try{
			$organization->setSetting(Organization::KANBANIZE_KEY_SETTING, $kanbanizeSettings, $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
			return new JsonModel([
				'boardId' => $id,
				'boardSettings' => $organization->getSetting(Organization::KANBANIZE_KEY_SETTING)['boards'][$id]
			]);
		}catch (\Exception $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(500);
		}
		return $this->response;
	}
	
	public function get($id){
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		if(!$this->isAllowed($this->identity(), $this->organization, 'Kanbanize.BoardSettings.get')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$importResults = $this->importer->importBoardColumns($organization, $this->identity(), $id);
		if(isset($importResults['errors'])){
			$error = new ErrorJsonModel();
			$error->setCode(400);
			$error->setDescription($importResults['errors']);
			$this->response->setStatusCode(400);
			return $error;
		}
		return new JsonModel([
			'boardId' => $id,
			'mapping' => $importResults['columns']
		]);
	}
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
}