<?php

namespace People\ValueObject;

class OrganizationParams
{
    public $params = [];

    private function __construct()
    {
        $this->params = [
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

    private function setInterval($data, $intervalName)
    {
        if (!isset($data[$intervalName]) ||
            !is_numeric($data[$intervalName])) {

            return;
        }

        $interval = new \DateInterval("P{$data[$intervalName]}D");
        $this->params[$intervalName] = $interval;
    }

    private function setIntValue($data, $intName)
    {
        if (!isset($data[$intName]) ||
            !is_numeric($data[$intName])) {

            return;
        }

        $this->params[$intName] = $data[$intName];
    }

    public function toArray()
    {
        $toString = function($item) {
            if ($item instanceof \DateInterval) {
                return $item->format('%d');
            }

            return (string) $item;
        };

        return array_map($toString, $this->params);
    }

    public function get($key)
    {
        if(!isset($this->params[$key])) {
            return null;
        }

        return $this->params[$key];
    }

    public static function createWithDefaults()
    {
        return new self();
    }

    public static function fromArray(array $data)
    {
        $settings = new self();

        $settings->setInterval($data, 'assignment_of_shares_timebox');
        $settings->setInterval($data, 'assignment_of_shares_remind_interval');
        $settings->setInterval($data, 'item_idea_voting_timebox');
        $settings->setInterval($data, 'item_idea_voting_remind_interval');
        $settings->setInterval($data, 'completed_item_voting_timebox');
        $settings->setInterval($data, 'completed_item_interval_close_task');

        $settings->setIntValue($data, 'tasks_limit_per_page');
        $settings->setIntValue($data, 'personal_transaction_limit_per_page');
        $settings->setIntValue($data, 'org_transaction_limit_per_page');
        $settings->setIntValue($data, 'org_members_limit_per_page');

        return $settings;
    }
}