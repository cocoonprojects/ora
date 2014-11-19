Feature: Login with SSO system
  In order to access subscribed user features
  As an unknown user  
  I want to sign in using my SSO system account

@login
Scenario: login for unknown user not authenticated in ORA Project
        Given I am not authenticated in ORA Project    
        When I go to "/" 
        Then I should see "Login"        

@login        
Scenario: login for unknown user not authenticated in ORA Project
        Given I am not authenticated in ORA Project
        When I go to "/"
		Then I click on "Login"
        And I should see popup "Effettua il login"        
	
       
#Scenario: Guest select Provider for authenticatin login
#        Given I am not authenticated in ORA Project
#        When I go to "/"
#		Then I click on "Login"
#        And I should see popup "Effettua il login"  
#        And I should see "Login con TestProvider"	        
#Scenario: Logged User
#        Given I am not authenticated in ORA Project
#        When I go to "/auth/login/testProvider?code=12345678901"
#        Then I should see "Logout"  
#        And I should see "Utente Test"      	     