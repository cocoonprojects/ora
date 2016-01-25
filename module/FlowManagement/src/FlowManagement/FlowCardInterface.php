<?php

namespace FlowManagement;

interface FlowCardInterface{
	
	CONST LAZY_MAJORITY_VOTE = 'LazyMajorityVote';
	
	public function getId();
	
	public function getRecipient();
	
	public function getMostRecentEditAt();
	
	public function getMostRecentEditBy();
	
	public function getContent();
}