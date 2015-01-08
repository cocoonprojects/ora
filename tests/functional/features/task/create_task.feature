Feature: Create task
	As an organization member
	I want to create a new task into one of my organization streams
	in order to allow the team to start the estimation

Scenario: Successfully creating a task into a stream and with a subject
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	And that its "subject" is "My First Task"
	And that its "streamID" is "00000000-1000-0000-0000-000000000000"
	When I request "/task-management/tasks"
	Then the response status code should be 201
	And the header 'Location' should be '/task-management/tasks/[0-9a-z\-]+'
	
Scenario: Cannot create a task without specifying the stream it belongs to and its subject
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	When I request "/task-management/tasks"
	Then the response status code should be 400

Scenario: Cannot create a task without specifying the stream it belongs to
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "streamID" is ""
	When I request "/task-management/tasks"
	Then the response status code should be 404

Scenario: Cannot create a task without specifying an existing stream it belongs to
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "streamID" is "00000000-xxxx-0000-0000-000000000000"
	When I request "/task-management/tasks"
	Then the response status code should be 404
	
Scenario: Cannot create a task without a subject
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	And that its "subject" is ""
	And that its "streamID" is "00000000-1000-0000-0000-000000000000"
	When I request "/task-management/tasks"
	Then the response status code should be 406