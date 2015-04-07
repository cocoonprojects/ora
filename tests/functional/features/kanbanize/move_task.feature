Feature: Move Task 
	As a task owner when I accept a Kanbanize defined task
	I want my acceptance to be reflected on Kanbanize itself 
	in order to advance the card processing
@wip
Scenario: Successfully accepting an already accepted Kanbanize Task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "accept"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000106/transitions"
	Then the response status code should be 204
@wip
Scenario: Succesfully completing a complete Kanbanize Task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "complete"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000107/transitions"
	Then the response status code should be 204
@wip
Scenario: Succesfully keep an ongoing Kanbanize Task in ongoing
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "execute"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000108/transitions"
	Then the response status code should be 204
@wip
Scenario: Cannot move an accepted Kanbanize Task to ongoing
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "execute"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000106/transitions"
	Then the response status code should be 412
@wip
Scenario: Cannot accept an ongoing Kanbanize Task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "accept"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000108/transitions"
	Then the response status code should be 412
@wip
Scenario: Succesfully moving back a completed Kanbanize Task to ongoing (107)
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "execute"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000110/transitions"
	Then the response status code should be 200
@wip
Scenario: Succesfully moving back an accepted Kanbanize Task to completed (106)
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "complete"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000111/transitions"
	Then the response status code should be 200
@wip
Scenario: Succesfully moving an ongoing Kanbanize Task to completed
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "complete"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000108/transitions"
	Then the response status code should be 200
@wip
Scenario: Cannot accept a non existing Kanbanize Task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "accept"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000088888888/transitions"
	Then the response status code should be 404
@wip
Scenario: Cannot move a non existing Kanbanize Task to ongoing
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "execute"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000088888888/transitions"
	Then the response status code should be 404
@wip
Scenario: Cannot move an existing Kanbanize Task to an non existing status 
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "pippo"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000108/transitions"
	Then the response status code should be 400
@wip
Scenario: Cannot accept a completed task with incomplete estimation
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Transaction"
	And that its "action" is "accept"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000001/transitions"
	Then the response status code should be 412
@wip
Scenario: Cannot accept a completed task if you are a member but not the owner (106)
	Given that I am authenticated as "paul.smith@ora.local"
	And that I want to make a new "Transaction"
	And that its "action" is "accept"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000107/transitions"
	Then the response status code should be 403
@wip
Scenario: Cannot complete a ongoing task if you are a member but not the owner
	Given that I am authenticated as "paul.smith@ora.local"
	And that I want to make a new "Transaction"
	And that its "action" is "complete"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000000/transitions"
	Then the response status code should be 403
@wip
Scenario: Cannot put in execution a completed task if you are a member but not the owner
	Given that I am authenticated as "paul.smith@ora.local"
	And that I want to make a new "Transaction"
	And that its "action" is "execute"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000001/transitions"
	Then the response status code should be 403
@wip
Scenario: Cannot complete an accepted task if you are a member but not the owner
	Given that I am authenticated as "paul.smith@ora.local"
	And that I want to make a new "Transaction"
	And that its "action" is "complete"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000002/transitions"
	Then the response status code should be 403
@wip
Scenario: Successfully accepting a completed Kanbanize Task (106)
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Transaction"
	And that its "action" is "accept"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000107/transitions"
	Then the response status code should be 200
