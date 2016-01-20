<?php
namespace FlowManagement;

use ZFX\Rest\Controller\HATEOASRestfulController;
use FlowManagement\Service\FlowService;
use FlowManagement\FlowCardInterface;

class CardsController extends HATEOASRestfulController {

	const DEFAULT_FLOW_ITEMS_LIMIT = 10;
	protected static $collectionOptions = ['GET'];
	protected static $resourceOptions = [];

	/**
	 * 
	 * @var FlowService
	 */
	private $flowService;
	/**
	 * @var integer
	 */
	protected $listLimit = self::DEFAULT_FLOW_ITEMS_LIMIT;

	public function __construct(FlowService $flowService){
		$this->flowService = $flowService;
	}

	public function getList(){

		if(is_null($this->identity())){
			$this->response->setStatusCode(401);
			return $this->response;
		}

		$integerValidator = new ValidatorChain();
		$integerValidator
			->attach(new IsInt())
			->attach(new GreaterThan(['min' => 0, 'inclusive' => false]));
		$offset = $this->getRequest()->getQuery("offset");
		$offset = $integerValidator->isValid($offset) ? intval($offset) : 0;
		$limit = $this->getRequest()->getQuery("limit");
		$limit = $integerValidator->isValid($limit) ? intval($limit) : $this->getListLimit();
		
		$flowCards = $this->flowService->findFlowCards($this->identity(), $offset, $limit);

		$count = count($flowItems);
		$hal['count'] = $count;
		$hal['total'] = $count;
		$hal['_links']['self']['href'] = $this->url()->fromRoute('flow');
		$hal['_embedded']['ora:flowcard'] = $count ? array_column(array_map([$this, 'serializeOne'], $flowCards), null, 'id') : new \stdClass();
		return new JsonModel($hal);
		echo("YEAH!!");
		die();
	}

	protected function getCollectionOptions(){
		return self::$collectionOptions;
	}

	protected function getResourceOptions(){
		return self::$resourceOptions;
	}

	protected function getFlowService(){
		return $this->flowService;
	}

	public function setListLimit($size){
		if(is_int($size)){
			$this->listLimit = $size;
		}
	}

	public function getListLimit(){
		return $this->listLimit;
	}

	protected function serializeOne($flowitem) {
		return [
				'id' => $flowitem->getId (),
				'createdAt' => date_format($flowitem->getCreatedAt(), 'c'),
				'contents' => $this->serializeContents($flowitem)
		];
	}
	protected function serializeContent($flowitem){
		//TODO: la serializzazione del content si può spostare nella singola classe che estende FlowCard?
		$contents = $flowitem->getContents();
		if(isset($contents[FlowCardInterface::LAZY_MAJORITY_VOTE])){
			return [
					'text' => 'Lazy Majority Voting New Item Idea',
					'_links' => [
						'ora:item' => [
							'href' => $this->url()->fromRoute('tasks', [
										'orgId' => $contents[FlowCardInterface::LAZY_MAJORITY_VOTE]['orgId'], 
										'id' => $contents[FlowCardInterface::LAZY_MAJORITY_VOTE]['itemId'] 
							]),
						],
					],
			];
		}
	}
}