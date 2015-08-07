Feature: Send a Reminder to Speed Up Task Estimation
	As an organization member
	I want to send a reminder 
	in order to speed up a task estimation in task with OnGoing status

Scenario: Successfully sending a reminder as task owner
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Reminder"
	And that its "type" is "add-estimation"
	When I request "/task-management/mail/00000000-0000-0000-0000-000000000004"
	Then the response status code should be 200
	
Scenario: Cannot sending a reminder as task member 
	Given that I am authenticated as "phil.toledo@ora.local" 
	And that I want to make a new "Reminder"
	And that its "type" is "add-estimation"
	When I request "/task-management/mail/00000000-0000-0000-0000-000000000004"
	Then the response status code should be 403
	
Scenario: Cannot sending a reminder as task owner with wrong parameter 
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Reminder"
	And that its "type" is "wrong-parameter"
	When I request "/task-management/mail/00000000-0000-0000-0000-000000000004"
	Then the response status code should be 405
	
Scenario: Cannot sending a reminder as task owner with null params 
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Reminder"
	When I request "/task-management/mail/00000000-0000-0000-0000-000000000004"
	Then the response status code should be 400	
	
Scenario: Cannot sending a reminder as task owner with empty params 
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Reminder"
	And that its "type" is ""
	When I request "/task-management/mail/00000000-0000-0000-0000-000000000004"
	Then the response status code should be 400	
	
Scenario: Cannot sending a reminder as task owner with wrong taskId
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Reminder"
	And that its "type" is "add-estimation"
	When I request "/task-management/mail/10000000-0000-0000-0000-000000000000"
	Then the response status code should be 404
