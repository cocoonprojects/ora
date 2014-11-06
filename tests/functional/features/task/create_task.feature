Feature: Create task
	As an organization member
	I want to create a new task into one of my organization projects
	in order to allow the team to start the estimation

@task @create @POST
Scenario: Successfully creating a new Task with just the reference project and a subject
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	And that its "subject" is "My First Task"
	And that its "projectID" is "00000000-1000-0000-0000-000000000000"
	When I request "/task-management/tasks"
	Then the response status code should be 201
	And the header 'Location' should be '/task-management/tasks/[0-9a-z\-]+'
	
@task @create @POST
Scenario: Cannot create a new Task with no parameters
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	When I request "/task-management/tasks"
	Then the response status code should be 400

@task @create @POST
Scenario: Cannot create a new Task with no reference project
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "projectID" is ""
	When I request "/task-management/tasks"
	Then the response status code should be 404

@task @create @POST
Scenario: Cannot create a new Task with not existing projectID
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "projectID" is "00000000-xxxx-0000-0000-000000000000"
	When I request "/task-management/tasks"
	Then the response status code should be 404
	
@task @create @POST
Scenario: Cannot create a new Task with empty subject
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	And that its "subject" is ""
	And that its "projectID" is "00000000-1000-0000-0000-000000000000"
	When I request "/task-management/tasks"
	Then the response status code should be 406