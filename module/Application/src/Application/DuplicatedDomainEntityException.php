<?php
namespace Application;

class DuplicatedDomainEntityException extends \DomainException {
	
	public function __construct(DomainEntity $container, $content) {
		parent::__construct(get_class($content).' '.$content->getId().' is already part of '.get_class($container).' '.$container->getId());
	}
	
}