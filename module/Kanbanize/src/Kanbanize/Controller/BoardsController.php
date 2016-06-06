<?php

namespace Kanbanize\Controller;

use Application\Controller\OrganizationAwareController;
use Application\InvalidArgumentException;
use Application\View\ErrorJsonModel;
use People\Service\OrganizationService;
use People\Organization;
use Kanbanize\Service\KanbanizeAPI;
use Kanbanize\Service\KanbanizeApiException;
use Kanbanize\Service\Importer;
use TaskManagement\Service\StreamService;
use TaskManagement\Task;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\View\Model\JsonModel;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Kanbanize\KanbanizeStream;
use Kanbanize\Service\KanbanizeService;


class BoardsController extends OrganizationAwareController{

	protected static $resourceOptions = ['POST', 'GET'];
	protected static $collectionOptions= [];

	protected static $valid_statuses = [
		Task::STATUS_IDEA,
		Task::STATUS_OPEN,
		Task::STATUS_ONGOING,
		Task::STATUS_COMPLETED,
		Task::STATUS_ACCEPTED,
		Task::STATUS_CLOSED,
		Task::STATUS_ARCHIVED
	];

	/**
	 * @var StreamService
	 */
	private $streamService;
	/**
	 * @var KanbanizeAPI
	 */
	private $client;
	/**
	 * @var KanbanizeService
	 */
	private $kanbanizeService;

	public function __construct(OrganizationService $organizationService, StreamService $streamService, KanbanizeAPI $client, KanbanizeService $kanbanizeService){
		parent::__construct($organizationService);
		$this->streamService = $streamService;
		$this->client = $client;
		$this->kanbanizeService = $kanbanizeService;
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
		$notEmptyValidator = new NotEmpty();
		$error = new ErrorJsonModel();
		if(!isset($data['projectId']) || !$notEmptyValidator->isValid($data['projectId'])){
			$error->addSecondaryErrors("projectId", ["Missing project id"]);
		}
		if(!isset($data['streamName'])){
			$error->addSecondaryErrors("streamName", ["Stream name cannot be empty"]);
		} else {
			$filters = new FilterChain();
			$filters->attach(new StringTrim())
				->attach(new StripNewlines())
				->attach(new StripTags());
			$streamName = $filters->filter($data['streamName']);
			if(!$notEmptyValidator->isValid($data['streamName'])){
				$error->addSecondaryErrors("streamName", ["Stream name cannot be empty"]);
			}
		}
		$statusValidator = new InArray([
			'haystack' => static::$valid_statuses
		]);

		$params = [$statusValidator, &$error];
		array_walk($data['mapping'], function($status, $column) use($params){
			$statusValidator = $params[0];
			$error = $params[1];
			if(!$statusValidator->isValid($status)){
				$error->addSecondaryErrors($column, ["Invalid status: {$status}"]);
			}
		});

		//all status should be mapped
		if(count($data['mapping']) < count(static::$valid_statuses)) {
			$error->addSecondaryErrors("mapping", ["Kanbanize board should have at least one column for each itm status"]);
		}

		if($error->hasErrors()){
			$error->setCode(400);
			$error->setDescription('Some parameters are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}

		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$kanbanizeSettings = $organization->getSettings(Organization::KANBANIZE_SETTINGS);
		$kanbanizeSettings['boards'][$id]['columnMapping'] = $data['mapping'];
		$stream = $this->kanbanizeService->findStreamByBoardId($id, $organization);
		$projectId = $data['projectId'];
		$this->transaction()->begin();
		try{
			if(is_null($stream)){
				$stream = $this->createStream($streamName, $projectId, $id, $organization);
			}else if($stream->getSubject() != $streamName){
				$stream->setSubject($streamName, $this->identity());
			}
			$organization->setSettings(Organization::KANBANIZE_SETTINGS, $kanbanizeSettings, $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
			return new JsonModel([
				'streamName' => $stream->getSubject(),
				'boardId' => $id,
				'boardSettings' => $organization->getSettings(Organization::KANBANIZE_SETTINGS)['boards'][$id]
			]);
		}catch (InvalidArgumentException $ex){
			$this->transaction()->rollback();
			$this->response->setStatusCode(422);
			$error->setCode(ErrorJsonModel::$ERROR_INPUT_VALIDATION);
			$error->setDescription($e->getMessage());
			return $error;
		}
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
		$kanbanizeSettings = $organization->getSettings(Organization::KANBANIZE_SETTINGS);
		$error = new ErrorJsonModel();
		try{
			$client = $this->initApi($kanbanizeSettings['apiKey'], $kanbanizeSettings['accountSubdomain']);
			$structure = $client->getBoardStructure($id);
			if(is_string($structure)){
				//TODO: il metodo getProjectsAndBoards, se va a buon fine, restituisce un array; in caso di errore non restituisce un messaggio completo ma solamente il primo carattere
				//migliorare questo comportamento
				$error->setCode(502);
				$error->setDescription("Cannot import structure for boardId: {$id}, due to: {$structure}");
				$this->response->setStatusCode(502);
				return $error;
			}
			$mappedColumns = array_map(function(){
					return "";
				}, array_flip(array_column($structure['columns'], 'lcname'))
			);
			if(isset($kanbanizeSettings['boards'][$id]['columnMapping'])){
				$mergedMapping = array_merge($mappedColumns, $kanbanizeSettings['boards'][$id]['columnMapping']);
				$mappedColumns = array_intersect_key($mergedMapping, $mappedColumns);
			}
			unset($mappedColumns['Temp Archive']); //Rimossa la possibilità di utilizzare la colonna Temp Archived di Kanbanize (non utilizzabile attraverso le APIs)
			$streamName = "";
			$stream = $this->kanbanizeService->findStreamByBoardId($id, $this->organization);
			if(!is_null($stream)){
				$streamName = $stream->getSubject();
			}
			return new JsonModel([
				'streamName' => $streamName,
				'boardId' => $id,
				'mapping' => $mappedColumns
			]);
		}catch (KanbanizeApiException $e){
			$error->setCode(400);
			$error->setDescription($e->getMessage());
			$this->response->setStatusCode(400);
			return $error;
		}
	}
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	private function initApi($apiKey, $subdomain){
		if(is_null($apiKey)){
			throw new KanbanizeApiException("Cannot connect to Kanbanize due to missing api key");
		}
		if(is_null($subdomain)){
			throw new KanbanizeApiException("Cannot connect to Kanbanize due to missing account subdomain");
		}
		$client = $this->client;
		$client->setApiKey($apiKey);
		$client->setUrl(sprintf(Importer::API_URL_FORMAT, $subdomain));
		return $client;
	}
	private function createStream($subject, $projectId, $boardId, Organization $organization) {
		$options = [
				'projectId' => $projectId,
				'boardId' => $boardId
		];
		$stream = KanbanizeStream::create($organization, $subject, $this->identity(), $options);
		$this->streamService->addStream($stream);
		return $stream;
	}
}