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
use Application\Entity\BasicUser;
use FlowManagement\VoteIdeaCard;

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
			->andWhere('f.hidden = false')
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
	public function createVoteIdeaCard(BasicUser $recipient, $itemId, $organizationid, BasicUser $createdBy){
		$content = [
				"orgId" => $organizationid
		];
		$this->eventStore->beginTransaction();
		try {
			$card = VoteIdeaCard::create($recipient, $content, $createdBy, $itemId);
			$this->addAggregateRoot($card);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			$this->eventStore->rollback();
			throw $e;
		}
		return $card;
	}
	/**
	 * (non-PHPdoc)
	 * @see \FlowManagement\Service\FlowService::countCards()
	 */
	public function countCards(BasicUser $recipient, $filters){
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('count(f)')
			->from(ReadModelFlowCard::class, 'f')
			->where('f.recipient = :recipient')
			->andWhere('f.hidden = false')
			->setParameter(':recipient', $recipient);
		
		return intval($query->getQuery()->getSingleScalarResult());
	}
}