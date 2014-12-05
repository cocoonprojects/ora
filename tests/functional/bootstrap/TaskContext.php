<?php 


class TaskContext extends RestContext
{
    public function __construct($base_url)
    {
    	parent::__construct($base_url);
    }

    private $_tasks = "";
    
 	/**
     * @Then /^exists "([^"]*)" with "([^"]*)" "([^"]*)"$/
     */
    public function executeCondition($searchArray, $searchParameter, $parameterValue)
    {
       	$tasks = json_decode($this->getResponse()->getBody(true))->tasks;
    	
       	$existingCounter = 0;
       	
       	if(!empty($tasks)){
	       	foreach($tasks as $task){
	       		
	       		$exists = true;
	       		
	       		if (isset($task->$searchArray)) {
	       		
		       		$data = $task->$searchArray;
					if(!empty($data)){
												
						foreach($data as $element){
			                if($parameterValue == 'not null'){
		                		if( !empty($element->$searchParameter)){
			                		//throw new Exception("Property {$searchParameter} is empty!\n");
			                		$existingCounter++;
		                		}		                		
		                	}else{
		                		if($element->$searchParameter == $parameterValue){
		                			//throw new Exception("Property {$searchParameter} value {$element->$searchParameter} doesn't match expected value {$parameterValue}!\n");
		                			$existingCounter++;
		                		}
		                	}
			            }	
					}
		            
		        }else {
		            throw new Exception(
		                    "{$searchArray} not found into tasks!");
		        }		
	       	}
	       		       	
       		if($existingCounter == 0){
				throw new Exception("Property {$searchParameter} value doesn't match expected value {$parameterValue}!\n");
			}
	       	
       	}else{
       	    throw new Exception(
                    "tasks is empty!");
       	}
       	
       	       
       
    }
    
    private function setTasks($tasks){
    	$this->_tasks = $tasks; 
    }
}