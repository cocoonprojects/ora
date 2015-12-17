<?php

namespace Kanbanize\Controller;

use Application\Controller\OrganizationAwareController;
use People\Service\OrganizationService;
use People\Organization;
use Kanbanize\Service\ImportDirector;

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
		if(!isset($data['columnMapping'])){
			$this->response->setStatusCode(400);
			return $this->response;
		}
		$organization = $this->getOrganizationService()->findOrganization($this->organization->getId());
		$kanbanizeSettings = $organization->getSetting(Organization::KANBANIZE_KEY_SETTING);
		$kanbanizeSettings['boards'][$id] = $data;
		$this->transaction()->begin();
		try{
			$organization->setSetting(Organization::KANBANIZE_KEY_SETTING, $kanbanizeSettings);
			$this->transaction()->commit();
			$this->response->setStatusCode(200);
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
		//TODO: return JSONMODEL
		var_dump($importResults);die();
	}
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
}