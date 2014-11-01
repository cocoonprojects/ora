<?php
namespace Ora\User;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;
use Rhumsaa\Uuid\Uuid;

/**
 * @ORM\Entity @ORM\Table(name="users")
 *
 */
class Profile extends DomainEntity implements \Serializable, MasterData
{

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

	public function __construct()
	{
	}
	
	public static function create(Uuid $id, \DateTime $createdAt = null, MasterData $createdBy = null) {
		$rv = new self();
		$rv->id = $id;
		$rv->createdAt = $createdAt == null ? new \DateTime() : $createdAt;
		if(!is_null($createdBy)) {
			$rv->createdBy = $createdBy->toProfile();
		}
		return $rv;
	}

	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
	}

	public function getFirstname()
	{
		return $this->firstname;
	}

	public function setLastname($lastname)
	{
		$this->lastname = $lastname;
	}

	public function getLastname()
	{
		return $this->lastname;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function getEmail()
	{
		return $this->email;
	}
	
	public function toProfile() {
		return $this;
	}

	public function serialize()
	{
		$data = array(
				'id' => $this->id->toString(),
				'email' => $this->email,
				'firstname' => $this->firstname,
				'lastname' => $this->lastname,
		);
		return serialize($data);
	}

	public function unserialize($encodedData)
	{
		$data = unserialize($encodedData);
		$this->id = Uuid::fromString($data['id']);
		$this->email = $data['email'];
		$this->firstname = $data['firstname'];
		$this->lastname = $data['lastname'];
	}
}