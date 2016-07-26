<?php

namespace Kanbanize\Controller;

use Application\Controller\OrganizationAwareController;
use Application\View\ErrorJsonModel;
use Kanbanize\Service\Importer;
use Kanbanize\Service\KanbanizeAPI;
use Kanbanize\Service\KanbanizeApiException;
use Kanbanize\Service\KanbanizeService;
use People\Service\OrganizationService;
use People\Organization;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\Validator\NotEmpty;
use Zend\View\Model\JsonModel;
use Zend\Validator\ValidatorChain;
use Zend\Validator\StringLength;
use Zend\Json\Json;

class SettingsController extends OrganizationAwareController
{
	protected static $resourceOptions = [];
	protected static $collectionOptions= ['PUT', 'GET'];

	private $client;

	private $kanbanizeService;

	public function __construct(
		OrganizationService $orgService,
		KanbanizeAPI $client,
		KanbanizeService $kanbanizeService
	)
	{
		parent::__construct($orgService);
		$this->client = $client;
		$this->kanbanizeService = $kanbanizeService;
	}

	public function getList()
	{
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $this->organization, 'Kanbanize.Settings.list')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$organization = $this->getOrganizationService()
			->getOrganization($this->organization->getId());

		$kanbanizeSettings = $organization->getSettings(Organization::KANBANIZE_SETTINGS);

		if(is_null($kanbanizeSettings) || empty($kanbanizeSettings)){
			return $this->getResponse()
						->setContent(json_encode(new \stdClass()));
		}

		try{
			$this->initApi(
				$kanbanizeSettings['apiKey'],
				$kanbanizeSettings['accountSubdomain']
			);

			$projects = $this->client
							 ->getProjectsAndBoards();

			if(!is_array($projects)){
				//TODO: il metodo getProjectsAndBoards, se va a buon fine, restituisce un array. Migliorare questo comportamento
				$error = new ErrorJsonModel();
				$error->setCode(400);
				$error->setDescription("Cannot import projects due to: The request cannot be processed. Please make sure you've specified all input parameters correctly");

				$this->response
					 ->setStatusCode(400);

				return $error;
			}

			$serializedProjects = $this->serializeProjects($projects, $organization);

			if(isset($serializedProjects['errors'])){
				$error = new ErrorJsonModel();
				$error->setCode(400);
				$error->setDescription($serializedProjects['errors']);
				$this->response->setStatusCode(400);
				return $error;
			}

			return new JsonModel([
					'apikey' => $kanbanizeSettings['apiKey'],
					'subdomain' => $kanbanizeSettings['accountSubdomain'],
					'projects' => $serializedProjects
			]);

		} catch(KanbanizeApiException $e){
			$error = new ErrorJsonModel();
			$error->setCode(400);
			$error->setDescription($e->getMessage());
			$this->response->setStatusCode(400);
			return $error;
		}
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

		$filters = new FilterChain();
		$filters->attach(new StringTrim())
			->attach(new StripNewlines())
			->attach(new StripTags());

		$subdomain = $filters->filter($data['subdomain']);
		$apiKey = $filters->filter($data['apiKey']);

		$organization = $this->getOrganizationService()
			->getOrganization($this->organization->getId());

		try{
			$this->initApi($apiKey, $subdomain);
			$projects = $this->client->getProjectsAndBoards();

			if(!is_array($projects)){
				//TODO: il metodo getProjectsAndBoards, se va a buon fine, restituisce un array; in caso di errore non restituisce un messaggio completo ma solamente il primo carattere
				//migliorare questo comportamento
				$error = new ErrorJsonModel();
				$error->setCode(400);
				$error->setDescription("Cannot import projects, the request cannot be processed. Please make sure you've specified all input parameters correctly (err: $projects)");
				$this->response->setStatusCode(400);
				return $error;
			}
			$serializedProjects = $this->serializeProjects($projects, $organization);

			if(isset($serializedProjects['errors'])){
				$error->setCode(400);
				$error->setDescription($serializedProjects['errors']);
				$this->response->setStatusCode(400);
				return $error;
			}

			$kanbanizeSettings = [
				'accountSubdomain' => $subdomain,
				'apiKey' => $apiKey
			];

			$this->transaction()->begin();
			$organization->setSettings(Organization::KANBANIZE_SETTINGS, $kanbanizeSettings, $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(202);
			return new JsonModel([
				'subdomain' => $organization->getSettings(Organization::KANBANIZE_SETTINGS)['accountSubdomain'],
				'projects' => $serializedProjects
			]);
		}catch(KanbanizeApiException $e){
			$error = new ErrorJsonModel();
			$error->setCode(400);
			$error->setDescription("Cannot import projects due to: {$e->getMessage()}");
			$this->response->setStatusCode(400);
			return $error;
		}catch(\Exception $e){
			$this->transaction()->rollback();
			$this->response->setStatusCode(500);
		}
	}

	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}

	protected function getResourceOptions() {
		return self::$resourceOptions;
	}

	protected function serializeProjects(&$projects, Organization $organization){
		try{
			$stream = $this->kanbanizeService
				 ->findStreamByOrganization($organization);

			foreach($projects as &$project){
				foreach($project['boards'] as &$board){
					$boardStructure = $this->client->getBoardStructure($board['id']);

					if(is_string($boardStructure)){
						$board['errors'] = [$boardStructure];
					}

					if ($boardStructure['columns'] < Organization::MIN_KANBANIZE_COLUMN_NUMBER) {
						$board['errors'] = ["Cannot import board due to the wrong columns number: must be at least ".Organization::MIN_KANBANIZE_COLUMN_NUMBER];
					}

					$board['columns'] = $boardStructure['columns'];
					$board['lanes'] = $boardStructure['lanes'];
					$board['streamName'] = '';
					$board['streamId'] = '';

					if(is_object($stream) &&
					   $stream->getBoardId() == $board['id']){
						$board['streamName'] = $stream->getSubject();
						$board['streamId'] = $stream->getId();
					}

				}
			}
		}catch (KanbanizeApiException $e){
			return ["errors" => ["Cannot import board structure due to: {$e->getMessage()}"]];
		}
		return $projects;
	}

	private function initApi($apiKey, $subdomain){
		if(is_null($apiKey)){
			throw new KanbanizeApiException("Cannot connect to Kanbanize due to missing api key");
		}
		if(is_null($subdomain)){
			throw new KanbanizeApiException("Cannot connect to Kanbanize due to missing account subdomain");
		}
		$this->client->setApiKey($apiKey);
		$this->client->setUrl(sprintf(Importer::API_URL_FORMAT, $subdomain));
	}

	public function getKanbanizeClient(){
		return $this->client;
	}
}