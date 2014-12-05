Feature: estimations list
    As an organization member
    I want to see who already assigned an estimation
    in order to understand how the estimation progress

@task @estimations @GET

     Scenario: Seeing who already estimated a task
        Given that I am authenticated as "mark.rogers@ora.local"
        And that I want to find a "members who estimated a task"
        When I request "/task-management/tasks"
		Then the response is JSON
        And exists "members" with "estimation" "not null"        
