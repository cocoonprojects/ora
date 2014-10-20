<?php

namespace Ora;

use Doctrine\ORM\Mapping AS ORM;
use Ora\EventStore\EventStore;

/**
 * @ORM\MappedSuperclass
 * @author andreabandera
 *
 */
class DomainEntity {

    /**
     * @ORM\Id @ORM\Column(type="string")
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     * @var datetime
     */
    private $mostRecentEditAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ora\User\User")
     */
    private $mostRecentEditBy;
    
    protected function __construct($id, \DateTime $createdAt)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function rebuild($events)
    {
        foreach ($events as $event)
        {
            $this->apply($event);
        }
    }

    public function setMostRecentEditAt($datetime) {
        $this->mostRecentEditAt = $datetime;
    }
    
    public function getMostRecentEditAt() {
        return $this->mostRecentEditAt;
    }
    
    public function setMostRecentEditBy($user) {
        $this->mostRecentEditBy = $user;
    }
    
    public function getMostRecentEditBy() {
        return $this->mostRecentEditBy;
    }
    
    private function apply(DomainEvent $domainEvent)
    {
        $method = 'apply'.get_class($domainEvent);
        $this->$method($domainEvent);
    }

}