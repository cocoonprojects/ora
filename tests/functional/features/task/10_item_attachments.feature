Feature: Items attachments
	As an item participant
	I want to attach some deliverables to the item
	in order to better describe the results obtained from that item

Scenario: Successfully creating a decision item
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to find a "Task"
    # Given that I am authenticated as "phil.toledo@ora.local"
	# And that I want to cast a new "Vote"
	# And that its "value" is "1"
	When I request "/60000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000020/attachments"
    Then the response status code should be 200
    And echo last response
    # And the response should have a "_embedded.ora:task" property
	# When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
	# Then the response status code should be 201
	# Then echo last response
	# And the header "Location" should be "/task-management/tasks/[0-9a-z\-]+"
	# And the response should be JSON
	# And the "decision" property should be "true"

