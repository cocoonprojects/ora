<?php

namespace FlowManagement;

interface FlowCardInterface{
	
	CONST VOTE_IDEA_CARD = 'VoteIdea';
	CONST VOTE_COMPLETED_ITEM_CARD = 'VoteCompletedItem';
	CONST VOTE_COMPLETED_ITEM_VOTING_CLOSED_CARD = 'VoteCompletedItemVotingClosed';
	CONST VOTE_COMPLETED_ITEM_REOPENED_CARD = 'VoteCompletedItemReopened';
	
	public function getId();
	
	public function getRecipient();
	
	public function getMostRecentEditAt();
	
	public function getMostRecentEditBy();
	
	public function getContent();
}