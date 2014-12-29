Feature: Create personal account
	As a user
	I want to have an account when I subscribe
	in order to collect and use my credits

@wip
Scenario: Successfully subscribing and checking the account
	Given that I am authenticated as "brian.colangelo@ora.local"
	And that I want to find a "Account"
	When I request "/accounting/accounts"
	Then the response status code should be 200
	And the response should be JSON
	And the "accounts" property size should be "1"
	