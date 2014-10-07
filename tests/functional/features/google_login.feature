#feature auth with SSO system
Feature: Login with SSO system
  In order to access subscribed user features
  As an unknown user  
  I want to sign in using my SSO system account

Scenario: login for unknown user not authenticated in ORA Project
        Given I am not authenticated in ORA Project
        When I go to "/"
        Then I should see "Login"
        And I don't see popup "Effettua il login"
        
Scenario: login for unknown user not authenticated in ORA Project
        Given I am not authenticated in ORA Project
        When I go to "/"
		Then I click on "Login"
        And I should see popup "Effettua il login"        
	
