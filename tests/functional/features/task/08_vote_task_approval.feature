Feature: Completed Work Item Voting (https://www.pivotaltracker.com/story/show/116529995)
	As an organization member
	I want to vote for (or against) a completed work item
	to contribute to the approval process

Scenario: One member cast a positive vote
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to cast a new "Vote"
	And that its "value" is "1"
	And that its "description" is "I like it"
	When I request "/60000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000020/acceptances"
	Then the response status code should be 201
	And the response should be JSON
	And the "status" property should be "30"
	And the response should have a "acceptances" property
	And the "acceptances" property size should be greater or equal than "1"

# Coupled with the "One member cast a positive vote" scenario
Scenario: One member can only vote a completed work item once
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to cast a new "Vote"
	And that its "value" is "1"
	When I request "/60000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000020/acceptances"
	Then the response status code should be 409

Scenario: Only task members are allowed to vote
 	Given that I am authenticated as "bruce.wayne@ora.local"
	And that I want to cast a new "Vote"
	And that its "value" is "1"
	When I request "/60000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000020/acceptances"
	Then the response status code should be 403

Scenario: Another one member cast a positive vote to accept the task
	Given that I am authenticated as "paul.smith@ora.local"
	And that I want to cast a new "Vote"
	And that its "value" is "1"
	When I request "/60000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000020/acceptances"
	Then the response status code should be 201
	And the response should be JSON
	And the "status" property should be "40"
	And the response should have a "acceptances" property
	And the "acceptances" property size should be greater or equal than "1"

Scenario: The majority of the members cast a negative vote
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to cast a new "Vote"
	And that its "value" is "0"
	And that its "description" is "I like it"
	When I request "/60000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000021/acceptances"
	And that I am authenticated as "paul.smith@ora.local"
	And that I want to cast a new "Vote"
	And that its "value" is "0"
	And that its "description" is "I like it"
	And I request "/60000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000021/acceptances"
	Then echo last response
	And the response status code should be 201
	And the response should be JSON
	And the "status" property should be "20"
	And the response should have a "acceptances" property
	And the "acceptances" property size should be "0"
