<?php

namespace FlowManagement;

interface FlowCardInterface{
	
	CONST VOTE_IDEA_CARD = 'VoteIdea';
	CONST VOTE_COMPLETED_ITEM_CARD = 'VoteCompletedItem';
	
	public function getId();
	
	public function getRecipient();
	
	public function getMostRecentEditAt();
	
	public function getMostRecentEditBy();
	
	public function getContent();
}