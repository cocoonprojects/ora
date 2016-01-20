<?php

namespace FlowManagement;

use Rhumsaa\Uuid\Uuid;
use Application\Entity\BasicUser;

class LazyMajorityVoteCard extends FlowCard{
	
	//FIXME: Declaration of FlowManagement\LazyMajorityVoteCard::create() should be compatible with FlowManagement\FlowCard::create(Application\Entity\BasicUser $recipient, $content, Application\Entity\BasicUser $by)
	public static function create(BasicUser $recipient, $content, BasicUser $createdBy){
		$rv = parent::create($recipient, $content, $createdBy);
		return $rv;
	} 
}