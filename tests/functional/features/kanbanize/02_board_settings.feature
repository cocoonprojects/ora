Feature: Kanbanize Board Settings
	As an organization admin
	I want to retrieve Kanbanize board structure 
	in order to associate to each column one of ORA task status 

	Scenario: Cannot get board settings as organization not admin
		Given that I am authenticated as "phil.toledo@ora.local"
		And that I want to find a "Board Settings"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings/boards/1"
		Then the response status code should be 403
	
	Scenario: Successfully getting board settings
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to find a "Board Settings"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings/boards/1"
		Then the response status code should be 200
		And the response should be JSON
		And the "boardId" property should be "1"
		And the response should have a "mapping" property
		And the "mapping.Requested" property should be ""
		And the "mapping.Approved" property should be ""
		And the "mapping.WIP" property should be ""
		And the "mapping.Testing" property should be ""
		And the "mapping.Accepted" property should be ""
		And the "mapping.Closed" property should be ""
		And the "mapping.Archive" property should be ""
	
	Scenario: Cannot create board settings as organization not admin
		Given that I am authenticated as "phil.toledo@ora.local"
		And that I want to make a new "Board Settings"
		And that its "Requested" is "0"
		And that its "Approved" is "10"
		And that its "WIP" is "20"
		And that its "Testing" is "20"
		And that its "Production_Release" is "30"
		And that its "Accepted" is "40"
		And that its "Closed" is "50"
		And that its "Archive" is "50"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings/boards/1"
		Then the response status code should be 403
	
	Scenario: Cannot create board settings with wrong column mapping status
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to make a new "Board Settings"
		And that its "projectId" is "1"
		And that its "streamName" is "foo stream"
		And that its "Requested" is "0"
		And that its "Approved" is "10"
		And that its "WIP" is "20"
		And that its "Testing" is "20"
		And that its "Production_Release" is "300"
		And that its "Accepted" is "40"
		And that its "Closed" is "50"
		And that its "Archive" is "500"
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
		And that its "Requested" is "0"
		And that its "Approved" is "10"
		And that its "WIP" is "20"
		And that its "Testing" is "20"
		And that its "Production_Release" is "30"
		And that its "Accepted" is "40"
		And that its "Closed" is "50"
		And that its "Archive" is "50"
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
		And that its "Requested" is "0"
		And that its "Approved" is "10"
		And that its "WIP" is "20"
		And that its "Testing" is "20"
		And that its "Production_Release" is "30"
		And that its "Accepted" is "40"
		And that its "Closed" is "50"
		And that its "Archive" is "50"
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
		
	Scenario: Successfully getting board settings
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to find a "Board Settings"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings/boards/1"
		Then the response status code should be 200
		And the response should be JSON
		And the "boardId" property should be "1"
		And the response should have a "mapping" property
		And the "mapping.Requested" property should be "0"
		And the "mapping.Approved" property should be "10"
		And the "mapping.WIP" property should be "20"
		And the "mapping.Testing" property should be "20"
		And the "mapping.Accepted" property should be "40"
		And the "mapping.Closed" property should be "50"
		And the "mapping.Archive" property should be "50"