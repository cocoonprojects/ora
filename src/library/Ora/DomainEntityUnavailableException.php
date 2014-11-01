<?php
namespace Ora;

use Ora\DomainEntity;

class DomainEntityUnavailableException extends \DomainException {
	
	public function __construct(DomainEntity $container, DomainEntity $content) {
		parent::__construct(get_class($content).' '.$content->getId()->toString().' isn\'t contained by '.get_class($container).' '.$container->getId()->toString);
	}
	
}