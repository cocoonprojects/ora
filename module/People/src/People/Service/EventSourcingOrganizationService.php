<?php

namespace People\Service;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use People\Organization;
use People\Entity\Organization as ReadModelOrg;
use People\Entity\OrganizationMembership;

class EventSourcingOrganizationService extends AggregateRepository implements OrganizationService
{
	/**
	 *
	 * @var EntityManager
	 */
	protected $entityManager;

	public function __construct(EventStore $eventStore, EntityManager $entityManager)
	{
		parent::__construct($eventStore, new AggregateTranslator(), new SingleStreamStrategy($eventStore), AggregateType::fromAggregateRootClass(Organization::class));
		$this->entityManager = $entityManager;
	}

	/**
	 * @param string $name
	 * @param User $createdBy
	 * @return Organization
	 * @throws \Exception
	 */
	public function createOrganization($name, User $createdBy) {
		$this->eventStore->beginTransaction();
		try {
			$org = Organization::create($name, $createdBy);
			$this->addAggregateRoot($org);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			$this->eventStore->rollback();
			throw $e;
		}
		return $org;
	}

	/**
	 * @param string|Uuid $id
	 * @return null|object
	 */
	public function getOrganization($id) {
		$oId = $id instanceof Uuid ? $id->toString() : $id;
		$rv = $this->getAggregateRoot($oId);
		return $rv;
	}

	/**
	 * @param string $id
	 * @return null|object
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 */
	public function findOrganization($id)
	{
		$rv = $this->entityManager->find(ReadModelOrg::class, $id);
		return $rv;
	}

	/**
	 * @param User $user
	 * @return array
	 */
	public function findUserOrganizationMemberships(User $user)
	{
		$rv = $this->entityManager->getRepository(OrganizationMembership::class)->findBy(['member' => $user], ['createdAt' => 'ASC']);
		return $rv;
	}

	/**
	 * @param ReadModelOrg $organization
	 * @param integer $offset
	 * @param integer $limit
	 * @return array
	 */
	public function findOrganizationMemberships(ReadModelOrg $organization, $limit, $offset, $roles=[])
	{
		$criteria = ['organization' => $organization];
		// diff contains elements not available in the second array, so not good
		$diff = array_diff($roles, [OrganizationMembership::ROLE_ADMIN, OrganizationMembership::ROLE_MEMBER, OrganizationMembership::ROLE_CONTRIBUTOR]);

		if (!empty($roles) && empty($diff)) {
			$criteria['role'] = $roles;
		}

		$rv = $this->entityManager->getRepository(OrganizationMembership::class)->findBy($criteria, ['createdAt' => 'ASC'], $limit, $offset);

		return $rv;

	}

	/**
	 * @param ReadModelOrg $organization
	 * @return integer
	 */
	public function countOrganizationMemberships(ReadModelOrg $organization, $roles=[]){
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('count(om.member)')
			->from(OrganizationMembership::class, 'om')
			->where('om.organization = :organization')
			->setParameter(':organization', $organization)
			;

		// diff contains elements not available in the second array, so not good
		$diff = array_diff($roles, [OrganizationMembership::ROLE_ADMIN, OrganizationMembership::ROLE_MEMBER, OrganizationMembership::ROLE_CONTRIBUTOR]);
		if (!empty($roles) && empty($diff)) {
			$query
				->andWhere('om.role in (:roles)')
				->setParameter(':roles', $roles);
		}
		return intval($query->getQuery()->getSingleScalarResult());
	}
	
	/**
	 * @return array
	 */
	public function findOrganizations()
	{
		$rv = $this->entityManager->getRepository(ReadModelOrg::class)->findBy([], ['name' => 'ASC']);
		return $rv;
	}
}
