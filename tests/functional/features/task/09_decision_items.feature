Feature: Decistion Items task
	As an organization member
	I want to create a decistion items into one of my organization streams
	in order to allow the team to start the estimation

Scenario: Successfully creating a decision item
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Decision Item"
	And that its "subject" is "My First Decision item"
	And that its "description" is "It's a new decision item"
	And that its "decision" is "true"
	And that its "streamID" is "00000000-1000-0000-0000-000000000000"
	And that its "status" is "0"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
	Then the response status code should be 201
	# Then echo last response
	And the header "Location" should be "/task-management/tasks/[0-9a-z\-]+"
	And the response should be JSON
	And the "decision" property should be "true"

