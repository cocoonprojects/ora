<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\User\User;

/**
 * 
 * @author andreabandera
 *
 */
abstract class EditableEntity extends DomainEntity {
	
    /**
     * @ORM\Column(type="datetime")
     * @var datetime
     */
    protected $mostRecentEditAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ora\User\User")
     * @ORM\JoinColumn(name="mostRecentEditBy_id", referencedColumnName="id", nullable=TRUE)
     */
    protected $mostRecentEditBy;
    
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

}