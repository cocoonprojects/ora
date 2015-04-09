Feature: Stream Creation
	As an organization member
	I want to create a new stream
	in order to collect related tasks

Scenario: Cannot create a stream anonymously
	Given that I want to make a new "Stream"
	And that its "subject" is "My First Stream"
	And that its "organizationId" is "00000000-0000-0000-1000-000000000000"
	When I request "/task-management/streams"
	Then the response status code should be 401

Scenario: Successfully creating a stream
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Stream"
	And that its "subject" is "My First Stream"
	And that its "organizationId" is "00000000-0000-0000-1000-000000000000"
	When I request "/task-management/streams"
	Then the response status code should be 201
	And the header "Location" should be "/task-management/streams/[0-9a-z\-]+"
	
Scenario: Successfully creating a stream without a subject
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Stream"
	And that its "organizationId" is "00000000-0000-0000-1000-000000000000"
	When I request "/task-management/streams"
	Then the response status code should be 201
	And the header "Location" should be "/task-management/streams/[0-9a-z\-]+"
	
Scenario: Cannot create a stream without providing a managing organization
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Stream"
	And that its "subject" is "My First Stream"
	When I request "/task-management/streams"
	Then the response status code should be 400

Scenario: Cannot create a stream providing a non existing managing organization
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Stream"
	And that its "subject" is "My First Stream"
	And that its "organizationId" is "00000000-0000-0000-x000-000000000000"
	When I request "/task-management/streams"
	Then the response status code should be 404