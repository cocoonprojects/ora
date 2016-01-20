<?php

namespace FlowManagement\Service;

use Doctrine\ORM\EntityManager;
use FlowManagement\Entity\FlowCard as ReadModelFlowCard;
use FlowManagement\FlowCard;
use Application\Entity\User;
use Rhumsaa\Uuid\Uuid;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use FlowManagement\LazyMajorityVoteCard;
use Application\Entity\BasicUser;

class EventSourcingFlowService extends AggregateRepository implements FlowService{

	/**
	 *
	 * @var EntityManager
	 */
	private $entityManager;

	public function __construct(EventStore $eventStore, EntityManager $entityManager) {
		parent::__construct($eventStore, new AggregateTranslator(), new SingleStreamStrategy($eventStore), AggregateType::fromAggregateRootClass(FlowCard::class));
		$this->entityManager = $entityManager;
	}

	/**
	 * (non-PHPdoc)
	 * @see \FlowManagement\Service\FlowService::findFlowCards()
	 */
	public function findFlowCards(User $recipient, $offset, $limit, $filters = []){

		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('f')
			->from(ReadModelFlowCard::class, 'f')
			->where('f.recipient = :recipient')
			->orderBy('f.createdAt', 'DESC')
			->setFirstResult($offset)
			->setMaxResults($limit)
			->setParameter(':recipient', $recipient);
		
		return $query->getQuery()->getResult();
	}
	/**
	 * (non-PHPdoc)
	 * @see \FlowManagement\Service\FlowService::createLazyMajorityVoteCard()
	 */
	public function createLazyMajorityVoteCard(BasicUser $recipient, $itemId, $organizationid, BasicUser $createdBy){
		$content = [
				"itemId" => $itemId,
				"orgId" => $organizationid
		];
		$this->eventStore->beginTransaction();
		try {
			$card = LazyMajorityVoteCard::create($recipient, $content, $createdBy);
			$this->addAggregateRoot($card);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			try {
				$this->eventStore->rollback();
			} catch (RuntimeException $e1) {
				// da loggare
			}
			throw $e;
		}
		return $card;
	}
}