<?php

namespace People\ValueObject;

class OrganizationParams
{
    public $params_defaults = [];

    public function __construct()
    {
        $this->params_defaults = [
            'assignment_of_shares_timebox' => new \DateInterval('P10D'),
            'assignment_of_shares_remind_interval' => new \DateInterval('P7D'),
            'item_idea_voting_timebox' => new \DateInterval('P7D'),
            'item_idea_voting_remind_interval' => new \DateInterval('P5D'),
            'completed_item_voting_timebox' => new \DateInterval('P7D'),
            'completed_item_interval_close_task' => new \DateInterval('P10D'),

            'tasks_limit_per_page' => 10,
            'personal_transaction_limit_per_page' => 10,
            'org_transaction_limit_per_page' => 10,
            'org_members_limit_per_page' => 20,
        ];
    }

    public function get($key) {
        if(!isset($this->params_defaults[$key])) {
            return null;
        }

        return $this->params_defaults[$key];
    }
}