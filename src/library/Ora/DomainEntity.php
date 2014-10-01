<?php
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

    private $eventStore;

    protected function __construct($id, \DateTime $createdAt, EventStore $eventStore)
    {
        $this->setId($id);
        $this->setCreatedAt($createdAt);
        $this->setEventStore($eventStore);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function setEventStore($eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function rebuild($events)
    {
        foreach ($events as $event)
        {
            $this->apply($event);
        }
    }

    protected function appendToStream(DomainEvent $domainEvent)
    {
        return $this->eventStore->appendToStream($domainEvent);
    }

    private function apply(DomainEvent $domainEvent)
    {
        $method = 'apply'.get_class($domainEvent);
        $this->$method($domainEvent);
    }
}