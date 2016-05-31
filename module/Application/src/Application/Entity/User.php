<?php

namespace Application\Entity;

use Application\InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use People\Entity\Organization as ReadModelOrganization;
use People\Entity\OrganizationMembership;
use People\Organization;
use Rhumsaa\Uuid\Uuid;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use FlowManagement\Entity\FlowCard;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 *
 */
class User extends BasicUser implements RoleInterface , ResourceInterface
{
	CONST STATUS_ACTIVE = 1;
	CONST ROLE_ADMIN = 'admin';
	CONST ROLE_GUEST = 'guest';
	CONST ROLE_USER = 'user';
	CONST ROLE_SYSTEM = 'system';

	CONST SYSTEM_USER = '00000000-0000-0000-0000-000000000000';

	CONST EVENT_CREATED = "User.Created";

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $createdAt;
	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="createdBy_id", referencedColumnName="id", nullable=TRUE)
	 * @var BasicUser
	 */
	protected $createdBy;
	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $mostRecentEditAt;
	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="mostRecentEditBy_id", referencedColumnName="id", nullable=TRUE)
	 * @var BasicUser
	 */
	protected $mostRecentEditBy;
	/**
	 * @ORM\Column(type="string", length=200, unique=TRUE)
	 * @var string
	 */
	private $email;
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	private $picture;
	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	private $status;
	/**
	 * @ORM\OneToMany(targetEntity="People\Entity\OrganizationMembership", mappedBy="member", indexBy="organization_id", cascade={"persist"})
	 * @var OrganizationMembership[]
	 */
	private $memberships;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $role = self::ROLE_USER;
	/**
	* @ORM\Column(type="string", nullable=TRUE)
	* @var string
	*/
	private $kanbanizeUsername;
	/**
	 * @ORM\OneToMany(targetEntity="FlowManagement\Entity\FlowCard", mappedBy="recipient", cascade={"persist"})
	 * @var FlowCard[]
	 */
	private $flowcards;

	private function __construct() {
		$this->memberships = new ArrayCollection();
		$this->flowcards = new ArrayCollection();
	}

	public static function create(User $createdBy = null) {
		$rv = new self();
		$rv->id = Uuid::uuid4()->toString();
		$rv->status = self::STATUS_ACTIVE;
		$rv->createdAt = new \DateTime();
		$rv->createdBy = $createdBy;
		$rv->mostRecentEditAt = $rv->createdAt;
		$rv->mostRecentEditBy = $rv->createdBy;
		return $rv;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt() {
		return $this->createdAt;
	}

	/**
	 * @param \DateTime $when
	 * @return $this
	 */
	public function setCreatedAt(\DateTime $when) {
		$this->createdAt = $when;
		return $this;
	}

	/**
	 * @return BasicUser
	 */
	public function getCreatedBy()
	{
		return $this->createdBy;
	}

	/**
	 * @param BasicUser $user
	 * @return $this
	 */
	public function setCreatedBy(BasicUser $user) {
		$this->createdBy = $user;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getMostRecentEditAt() {
		return $this->mostRecentEditAt;
	}

	/**
	 * @param \DateTime $when
	 * @return $this
	 */
	public function setMostRecentEditAt(\DateTime $when) {
		$this->mostRecentEditAt = $when;
		return $this;
	}

	/**
	 * @return BasicUser
	 */
	public function getMostRecentEditBy() {
		return $this->mostRecentEditBy;
	}

	/**
	 * @param BasicUser $user
	 * @return $this
	 */
	public function setMostRecentEditBy(BasicUser $user) {
		$this->mostRecentEditBy = $user;
		return $this;
	}

	/**
	 * @param User|null $object
	 * @return bool
	 */
	public function equals(User $object = null) {
		if(is_null($object)) {
			return false;
		}
		return $this->id == $object->getId();
	}

	/**
	 * @param $email
	 * @return $this
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param $status
	 * @return $this
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	public function getOrganizationMemberships()
	{
		return $this->memberships->toArray();
	}

	/**
	 * @param ReadModelOrganization|Organization $organization
	 * @param string $role
	 * @return $this
	 */
	public function addMembership($organization, $role = OrganizationMembership::ROLE_CONTRIBUTOR) {
		$org = null;
		if($organization instanceof Organization) {
			$org = new ReadModelOrganization($organization->getId());
			$org->setName($organization->getName());
		} elseif ($organization instanceof ReadModelOrganization) {
			$org = $organization;
		} else {
			throw new InvalidArgumentException('First argument must be of type People\\Organization or People\\Entity\\Organization: ' . get_class($organization) . ' given');
		}
		$membership = new OrganizationMembership($this, $org, $role);
		$this->memberships->set($org->getId(), $membership);
		return $this;
	}

	/**
	 * @param ReadModelOrganization|Organization $organization
	 * @return $this
	 */
	public function removeMembership($organization) {
		if(!($organization instanceof Organization) && !($organization instanceof ReadModelOrganization)) {
			throw new InvalidArgumentException('First argument must be of type People\\Organization or People\\Entity\\Organization: ' . get_class($organization) . ' given');
		}
		$this->memberships->remove($organization->getId());
	}

	public function setPicture($url) {
		$this->picture = $url;
		return $this;
	}

	public function getPicture() {
		return $this->picture;
	}

	/**
	 *
	 * @param string|ReadModelOrganization|Organization $organization
	 * @return bool
	 */
	public function isMemberOf($organization) {
		$key = $organization;
		if($organization instanceof Organization || $organization instanceof ReadModelOrganization) {
			$key = $organization->getId();
		}
		return $this->memberships->containsKey($key);
	}

	public function isContributorOf($organization) {
		$key = $organization;
		if($organization instanceof Organization ||
		   $organization instanceof ReadModelOrganization) {
			$key = $organization->getId();
		}
		$membership = $this->memberships->get($key);

		if(is_null($membership)){
			return false;
		}

		return $membership->getRole() == OrganizationMembership::ROLE_CONTRIBUTOR;
	}

	public function isRoleMemberOf($organization) {
		$key = $organization;
		if($organization instanceof Organization ||
		   $organization instanceof ReadModelOrganization) {
			$key = $organization->getId();
		}
		$membership = $this->memberships->get($key);

		if(is_null($membership)){
			return false;
		}

		return $membership->getRole() == OrganizationMembership::ROLE_MEMBER;
	}

	/**
	 *
	 * @param string|ReadModelOrganization|Organization $organization
	 * @return bool
	 */
	public function isOwnerOf($organization) {
		$key = $organization;
		if($organization instanceof Organization || $organization instanceof ReadModelOrganization) {
			$key = $organization->getId();
		}
		$membership = $this->memberships->get($key);
		if(is_null($membership)){
			return false;
		}
		return $membership->getRole() == OrganizationMembership::ROLE_ADMIN;
	}

	/**
	 * @param string|ReadModelOrganization|Organization $organization
	 * @return OrganizationMembership|null
	 */
	public function getMembership($organization){
		$id = is_object($organization) ? $organization->getId() : $organization;
		return $this->memberships->get($id);
	}

	public function setRole($role){
		$this->role = $role;
		return $this;
	}

	public function getRole() {
		return $this->role;
	}

	public function getRoleId(){
		return $this->getRole();
	}

	public function setKanbanizeUsername($username){
		$this->kanbanizeUsername = $username;
		return $this;
	}

	public function getKanbanizeUsername(){
		return $this->kanbanizeUsername;
	}

	public function getFlowCards(){
		return $this->flowcards;
	}

	public function addFlowCard(FlowCard $card){
		$this->flowcards[] = $card;
		return $this;
	}

	public function getResourceId()
	{
		return 'Ora\User';
	}
}