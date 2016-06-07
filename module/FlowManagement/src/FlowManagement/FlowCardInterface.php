<?php

namespace FlowManagement;

interface FlowCardInterface{
	
	CONST VOTE_IDEA_CARD = 'VoteIdea';
	CONST VOTE_COMPLETED_ITEM_CARD = 'VoteCompletedItem';
	CONST VOTE_COMPLETED_ITEM_VOTING_CLOSED_CARD = 'VoteCompletedItemVotingClosed';
	CONST VOTE_COMPLETED_ITEM_REOPENED_CARD = 'VoteCompletedItemReopened';
	CONST ITEM_OWNER_CHANGED_CARD = 'ItemOwnerChanged';
	CONST ITEM_MEMBER_REMOVED_CARD = 'ItemMemberRemoved';
	CONST ORGANIZATION_MEMBER_ROLE_CHANGED_CARD = 'OrganizationMemberRoleChanged';
	
	public function getId();
	
	public function getRecipient();
	
	public function getMostRecentEditAt();
	
	public function getMostRecentEditBy();
	
	public function getContent();
}