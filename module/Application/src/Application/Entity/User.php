<?php

namespace Application\Entity;

use Zend\Permissions\Acl\Role\RoleInterface;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use People\Entity\Organization;
use People\Entity\OrganizationMembership;


/**
 * @ORM\Entity @ORM\Table(name="users")
 *
 */
class User implements RoleInterface
{	   
	CONST STATUS_ACTIVE = 1;
	CONST ROLE_ADMIN = 'admin';
	CONST ROLE_GUEST = 'guest';
	CONST ROLE_USER = 'user';
	CONST ROLE_SYSTEM = 'system';
	
	CONST SYSTEM_USER = '00000000-0000-0000-0000-000000000000';
	
	CONST EVENT_CREATED = "User.Created";
	
	/**
	 * @ORM\Id @ORM\Column(type="string") 
	 * @var string
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $createdAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="createdBy_id", referencedColumnName="id", nullable=TRUE)
	 */
	protected $createdBy;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var datetime
	 */
	protected $mostRecentEditAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="mostRecentEditBy_id", referencedColumnName="id", nullable=TRUE)
	 */
	protected $mostRecentEditBy;
	
	/**
	 * @ORM\Column(type="string", length=100, nullable=TRUE)
	 * @var string
	 */
	private $firstname;

	/**
	 * @ORM\Column(type="string", length=100, nullable=TRUE)
	 * @var string
	 */
	private $lastname;

	/**
	 * @ORM\Column(type="string", length=200, unique=TRUE)
	 * @var string
	 */
	private $email;
	
	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	private $status;
	
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	private $picture;
	
	/**
	 * @ORM\OneToMany(targetEntity="People\Entity\OrganizationMembership", mappedBy="member", indexBy="organization_id", fetch="EAGER")
	 * @var OrganizationMembership[]
	 */
	private $memberships;

	/**
	 * @ORM\Column(type="string")
	 * @var string 
	 */
	private $role;

	public function __construct(){
		$this->memberships = new ArrayCollection();
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

	public function getId() {
		return $this->id;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	public function setCreatedAt(\DateTime $when) {
		$this->createdAt = $when;
		return $this->createdAt;
	}
	
	public function getCreatedBy()
	{
		return $this->createdBy;
	}
	
	public function setCreatedBy(User $user) {
		$this->createdBy = $user;
		return $this->createdBy;
	}

	public function getMostRecentEditAt() {
		return $this->mostRecentEditAt;
	}
	
	public function setMostRecentEditAt(\DateTime $when) {
		$this->mostRecentEditAt = $when;
		return $this->mostRecentEditAt;
	}
	
	public function getMostRecentEditBy() {
		return $this->mostRecentEditBy;
	}
	
	public function setMostRecentEditBy(User $user) {
		$this->mostRecentEditBy = $user;
		return $this->mostRecentEditBy;
	}

	public function equals(User $object = null) {
		if(is_null($object)) {
			return false;
		}
		return $this->id == $object->getId();
	}
	
	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
		return $this;
	}
	
	public function getFirstname()
	{
		return $this->firstname;
	}

	public function setLastname($lastname)
	{
		$this->lastname = $lastname;
		return $this;
	}
	
	public function getLastname()
	{
		return $this->lastname;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}
	
	public function getEmail()
	{
		return $this->email;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}
	
	public function getStatus()
	{
		return $this->status;
	}	
	
	public function getOrganizationMemberships()
	{
		return $this->memberships->toArray();
	}

	/**
	 * @param Organization $organization
	 * @param string $role
	 * @return $this
	 */
	public function addMembership(Organization $organization, $role = OrganizationMembership::ROLE_MEMBER) {
		$membership = new OrganizationMembership($this, $organization, $role);
		$this->memberships->set($organization->getId(), $membership);
		return $this;
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
	 * @param string|Organization $organization
	 * @return bool
	 */
	public function isMemberOf($organization) {
		$key = $organization instanceof Organization ? $organization->getId() : $organization;
		return $this->memberships->containsKey($key);
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
}