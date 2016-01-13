Feature: Kanbanize Organization Settings
	As an organization admin
	I want to set the Kanbanize connection parameters for my organization
	in order to activate the Kanbanize syncronization

	Scenario: Cannot get connection parameters as organization not admin
		Given that I am authenticated as "phil.toledo@ora.local"
		And that I want to find a "Setting"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 403
	
	Scenario: Successfully getting connection parameters not yet set ({})
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to find a "Setting"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 200
		And the response should be JSON
		And the response should be empty map
	
	Scenario: Cannot set connection parameters as organization not admin
		Given that I am authenticated as "phil.toledo@ora.local"
		And that I want to update a "Setting"
		And that its "subdomain" is "foo"
		And that its "apiKey" is "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 403
	
	Scenario: Cannot set connection parameters without subdomain
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to update a "Setting"
		And that its "apiKey" is "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 400
		And the response should be JSON
		And the "code" property should be "400"
		And the "description" property should be "Some parameters are not valid"
		And the "errors[0].field" property should be "subdomain"
		And the "errors[0].message" property should be "Value cannot be empty"
	
	Scenario: Cannot set connection parameters without apiKey
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to update a "Setting"
		And that its "subdomain" is "foo"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 400
		And the response should be JSON
		And the "code" property should be "400"
		And the "description" property should be "Some parameters are not valid"
		And the "errors[0].field" property should be "apiKey"
		And the "errors[0].message" property should be "Value cannot be empty"
		
	Scenario: Cannot set connection parameters with apiKey longer than 40 chars
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to update a "Setting"
		And that its "subdomain" is "foo"
		And that its "apiKey" is "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAaaa"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 400
		And the response should be JSON
		And the "code" property should be "400"
		And the "description" property should be "Some parameters are not valid"
		And the "errors[0].field" property should be "apiKey"
		And the "errors[0].message" property should be "Value lenght cannot be greater than 40 chars"
	
	Scenario: Successfully setting connection parameters with subdomain and apiKey
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to update a "Setting"
		And that its "subdomain" is "foo"
		And that its "apiKey" is "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 202
		And the response should be JSON
		And the "subdomain" property should be "foo"
		And the "projects[0].name" property should be "foo project"
		And the "projects[0].boards[0].id" property should be "1"
		And the "projects[0].boards[0].name" property should be "board 1"
	
	Scenario: Successfully getting connection parameters
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to find a "Setting"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 200
		And the response should be JSON
		And the "subdomain" property should be "foo"
		And the "projects[0].name" property should be "foo project"
		And the "projects[0].boards[0].id" property should be "1"
		And the "projects[0].boards[0].name" property should be "board 1"
	
	Scenario: Successfully updating connection parameters
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to update a "Setting"
		And that its "subdomain" is "acme"
		And that its "apiKey" is "BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 202
		And the response should be JSON
		And the "subdomain" property should be "acme"
		And the "projects[0].name" property should be "foo project"
		And the "projects[0].boards[0].id" property should be "1"
		And the "projects[0].boards[0].name" property should be "board 1"
		
	Scenario: Successfully getting connection parameters
		Given that I am authenticated as "mark.rogers@ora.local"
		And that I want to find a "Setting"
		When I request "/00000000-0000-0000-1000-000000000000/kanbanize/settings"
		Then the response status code should be 200
		And the response should be JSON
		And the "subdomain" property should be "acme"
		And that its "apiKey" is "BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB"
		And the "projects[0].name" property should be "foo project"
		And the "projects[0].boards[0].id" property should be "1"
		And the "projects[0].boards[0].name" property should be "board 1"
