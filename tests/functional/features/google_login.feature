Feature: login with google
  In order to access subscribed user features
  As an unknown user  
  I want to sign in using my Google account

Scenario: login for unknown user not authenticated in Google
        Given I am not authenticated in ORA Project
        And I am not authenticated in Google
        When I am on ORA Project login form
        And I press "Login with Google"
	And I fill an existing Google account      
	Then I should be logged in
	
Scenario: login for unknown user already authenticated in Google
	Given I am not authenticated in ORA Project
	And I am authenticated in Google
	When I am on ORA Project login form
	And I press "Login with Google"
	Then I should be logged in