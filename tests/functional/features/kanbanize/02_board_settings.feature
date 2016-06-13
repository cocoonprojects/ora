Feature: Kanbanize Board Settings
	As an organization admin
	I want to retrieve Kanbanize board structure
	in order to associate to each column one of ORA task status

	Scenario: Cannot create board settings as organization not admin
		Given that I am authenticated as "phil.toledo@ora.local"
		And that I want to make a new "Board Settings"
		And that its "mapping[Requested]" is "0"
		And that its "mapping[Approved]" is "10"
		And that its "mapping[WIP]" is "20"
		And that its "mapping[Testing]" is "20"
		And that its "mapping[Production_Release]" is "30"
		And that its "mapping[Accepted]" is "40"
		And that its "mapping[Closed]" is "50"
		And that its "mapping[Archive]" is "50"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings/boards/1"
		Then the response status code should be 403

	Scenario: Cannot create board settings with wrong column mapping status
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to make a new "Board Settings"
		And that its "projectId" is "1"
		And that its "streamName" is "foo stream"
		And that its "mapping[Requested]" is "0"
		And that its "mapping[Approved]" is "10"
		And that its "mapping[WIP]" is "20"
		And that its "mapping[Testing]" is "20"
		And that its "mapping[Production_Release]" is "300"
		And that its "mapping[Accepted]" is "40"
		And that its "mapping[Closed]" is "50"
		And that its "mapping[Archive]" is "500"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings/boards/1"
		Then the response status code should be 400
		And the response should be JSON
		And the "code" property should be "400"
		And the "description" property should be "Some parameters are not valid"
		And the "errors[0].field" property should be "Production_Release"
		And the "errors[0].message" property should be "Invalid status: 300"
		And the "errors[1].field" property should be "Archive"
		And the "errors[1].message" property should be "Invalid status: 500"

	Scenario: Cannot create board settings without a valid projectId, boardId, streamName
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to make a new "Board Settings"
		And that its "mapping[Requested]" is "0"
		And that its "mapping[Approved]" is "10"
		And that its "mapping[WIP]" is "20"
		And that its "mapping[Testing]" is "20"
		And that its "mapping[Production_Release]" is "30"
		And that its "mapping[Accepted]" is "40"
		And that its "mapping[Closed]" is "50"
		And that its "mapping[Archive]" is "50"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings/boards/1"
		Then the response status code should be 400
		And the response should be JSON
		And the "code" property should be "400"
		And the "description" property should be "Some parameters are not valid"
		And the "errors[0].field" property should be "projectId"
		And the "errors[0].message" property should be "Missing project id"
		And the "errors[1].field" property should be "streamName"
		And the "errors[1].message" property should be "Stream name cannot be empty"

	Scenario: Successfully creating board settings
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to make a new "Board Settings"
		And that its "projectId" is "1"
		And that its "streamName" is "foo stream"
		And that its "mapping[Requested]" is "0"
		And that its "mapping[Approved]" is "10"
		And that its "mapping[WIP]" is "20"
		And that its "mapping[Testing]" is "20"
		And that its "mapping[Production_Release]" is "30"
		And that its "mapping[Accepted]" is "40"
		And that its "mapping[Closed]" is "50"
		And that its "mapping[Archive]" is "50"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings/boards/1"
		Then the response status code should be 201
		And the response should be JSON
		And the "boardId" property should be "1"
		And the "streamName" property should be "foo stream"
		And the "boardSettings.columnMapping.Requested" property should be "0"
		And the "boardSettings.columnMapping.Approved" property should be "10"
		And the "boardSettings.columnMapping.WIP" property should be "20"
		And the "boardSettings.columnMapping.Testing" property should be "20"
		And the "boardSettings.columnMapping.Production_Release" property should be "30"
		And the "boardSettings.columnMapping.Accepted" property should be "40"
		And the "boardSettings.columnMapping.Closed" property should be "50"
		And the "boardSettings.columnMapping.Archive" property should be "50"
