<?php
namespace Application;

class DomainEntityUnavailableException extends \DomainException {
	
	public function __construct(DomainEntity $container, $content) {
		parent::__construct(get_class($content).' '.$content->getId().' isn\'t contained by '.get_class($container).' '.$container->getId());
	}
	
}