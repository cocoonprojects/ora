<?php
namespace Application;

use Rhumsaa\Uuid\Uuid;

class DuplicatedDomainEntityException extends \DomainException {
	
	public function __construct(DomainEntity $container, $content) {
		$id = $content->getId() instanceof Uuid ? $content->getId()->toString() : $content->getId();
		$containerId = $container->getId() instanceof Uuid ? $container->getId()->toString() : $container->getId();
		parent::__construct(get_class($content).' '.$id.' is already part of '.get_class($container).' '.$containerId);
	}
	
}