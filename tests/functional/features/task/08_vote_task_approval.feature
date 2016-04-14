Feature: Completed Work Item Voting (https://www.pivotaltracker.com/story/show/116529995)
	As an organization member
	I want to vote for (or against) a completed work item
	to contribute to the approval process

Scenario: One member cast a positive vote
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to cast a new "Vote"
	And that its "value" is "1"
	When I request "/60000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000020/acceptances"
	Then the response status code should be 201
	And the response should be JSON
	And the "status" property should be "30"
	And echo last response
	And the response should have a "acceptances" property
	And the "acceptances" property size should be greater or equal than "1"

# Scenario: One member cast a negative vote
# 	Given that I am authenticated as "mark.rogers@ora.local"
# 	And that I want to find a "UserTaskMetrics"
# 	When I request "/00000000-0000-0000-1000-000000000000/task-management/member-stats/60000000-0000-0000-0000-000000000000"
# 	Then the response status code should be 200
# 	And the response should be JSON
# 	And the response should have a "averageDelta" property
# 	And the response should have a "creditsCount" property
# 	And the response should have a "ownershipsCount" property
# 	And the response should have a "membershipsCount" property

# Coupled with the "One member cast a positive vote" scenario
Scenario: One member can only vote a completed work item once
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to cast a new "Vote"
	And that its "value" is "1"
	When I request "/60000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000020/acceptances"
	Then the response status code should be 409

# Scenario: One member can only vote a completed work item once
# 	Given that I am authenticated as "fake@ora.local"
# 	And that I want to find a "UserTaskMetrics"
# 	When I request "/00000000-0000-0000-1000-000000000000/task-management/member-stats/60000000-0000-0000-0000-000000000000"
# 	Then the response status code should be 401