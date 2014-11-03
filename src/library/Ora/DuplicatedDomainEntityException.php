<?php
namespace Ora;

use Ora\DomainEntity;

class DuplicatedDomainEntityException extends \DomainException {
	
	public function __construct(DomainEntity $container, DomainEntity $content) {
		parent::__construct(get_class($content).' '.$content->getId()->toString().' is already part of '.get_class($container).' '.$container->getId()->toString);
	}
	
}