Feature: Items attachments
	As an item participant
	I want to attach some deliverables to the item
	in order to better describe the results obtained from that item

Scenario: Successfully creating a decision item
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to find a "Task"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000004"
    Then the response status code should be 200
    And the response should have a "attachments.0.name" property
    And the "attachments[0].name" property should be "Post OrientDB"