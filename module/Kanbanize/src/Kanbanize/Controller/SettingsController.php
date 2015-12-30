<?php

namespace Kanbanize\Controller;

use Application\Controller\OrganizationAwareController;
use Application\View\ErrorJsonModel;
use People\Service\OrganizationService;
use Zend\Validator\NotEmpty;
use Kanbanize\Service\ImportDirector;
use People\Organization;
use Zend\View\Model\JsonModel;
use Zend\Validator\ValidatorChain;
use Zend\Validator\StringLength;

class SettingsController extends OrganizationAwareController{
	
	protected static $resourceOptions = [];
	protected static $collectionOptions= ['PUT', 'GET'];
	/**
	 * @var ImportDirector
	 */
	private $importer;
	
	public function __construct(OrganizationService $orgService, ImportDirector $importer){
		parent::__construct($orgService);
		$this->importer = $importer;
	}
	
	public function getList(){
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		if(!$this->isAllowed($this->identity(), $this->organization, 'Kanbanize.Settings.list')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$kanbanizeSettings = $organization->getSetting(Organization::KANBANIZE_KEY_SETTING);
		if(is_null($kanbanizeSettings) || empty($kanbanizeSettings)){
			return new JsonModel([new \stdClass()]);
		}
		$projects = $this->importer->importProjects($organization, $this->identity(), $kanbanizeSettings['apiKey'], $kanbanizeSettings['accountSubdomain']);
		if(isset($projects['errors'])){
			$error = new ErrorJsonModel();
			$error->setCode(400);
			$error->addSecondaryErrors('kanbanize', $projects['errors']);
			$error->setDescription('Some parameters are not valid');
			return $error;
		}
		return new JsonModel([
			'subdomain' => $organization->getSetting(Organization::KANBANIZE_KEY_SETTING)['accountSubdomain'],
			'apiKey' => $organization->getSetting(Organization::KANBANIZE_KEY_SETTING)['apiKey'],
			'projects' => array_map([$this, 'serializeProjects'], $projects, array_fill(0, sizeof($projects), $organization))
		]);
	}

	public function replaceList($data){
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		if(!$this->isAllowed($this->identity(), $this->organization, 'Kanbanize.Settings.create')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$error = new ErrorJsonModel();
		$apiKeyValidator = new ValidatorChain();
		$apiKeyValidator
				->attach(new StringLength(['max' => 40]));
		if(!(isset($data['subdomain']))) {
			$error->addSecondaryErrors('subdomain', ['Value cannot be empty']);
		}
		if(!(isset($data['apiKey']))) {
			$error->addSecondaryErrors('apiKey', ['Value cannot be empty']);
		}elseif (!$apiKeyValidator->isValid($data['apiKey'])){
			$error->addSecondaryErrors('apiKey', ['Value lenght cannot be greater than 40 chars']);
		}
		if($error->hasErrors()) {
			$error->setCode(400);
			$error->setDescription('Some parameters are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}
		$subdomain = $data['subdomain'];
		$apiKey = $data['apiKey'];
		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$connectionResults = $this->importer->verifyConnection($organization, $this->identity(), $apiKey, $subdomain);
		if(isset($connectionResults['errors'])){
			$error->setCode(400);
			$error->setDescription($connectionResults['errors']);
			$this->response->setStatusCode(400);
			return $error;
		}
		$serializedProjects = array_map([$this, 'serializeProjects'], $connectionResults['projects'], array_fill(0, sizeof($connectionResults['projects']), $organization));
		if(isset($serializedProjects['errors'])){
			$error->setCode(400);
			$error->setDescription($serializedProjects['errors']);
			$this->response->setStatusCode(400);
			return $error;
		}
		$this->response->setStatusCode(202);
		return new JsonModel([
			'subdomain' => $organization->getSetting(Organization::KANBANIZE_KEY_SETTING)['accountSubdomain'],
			'apiKey' => $organization->getSetting(Organization::KANBANIZE_KEY_SETTING)['apiKey'],
			'projects' => $serializedProjects
		]);
	}

	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	
	protected function serializeProjects($project, $organization){
		foreach($project['boards'] as &$board){
			$importBoardStructureResult = $this->importer->importBoardStructure($organization, $this->identity(), $board['id']);
			if(isset($importBoardStructureResult['errors'])){
				return ['errors'=>$importBoardStructureResult['errors']];
			}
			$board['columns'] = $importBoardStructureResult ['columns'];
		}
		return $project;
	}
}