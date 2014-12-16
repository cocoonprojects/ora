<?php

namespace Ora\Kanbanize;

use Ora\Kanbanize\Exception\KanbanizeApiException;
class KanbanizeAPI {
	/**
	 *
	 * @var string YOUR API CALL
	 */
	// protected $kanbanize_url = 'http://kanbanize.com/index.php/api/kanbanize';
	
	/**
	 *
	 * @param string $k        	
	 *
	 * @return $this
	 */
	public function setApiKey($k) {
		$this->api_key = $k;
		return $this;
	}
	
	/**
	 *
	 * @param string $url        	
	 *
	 * @return $this
	 */
	public function setUrl($url) {
		$this->kanbanize_url = $url;
		return $this;
	}
	protected function executeCall(KanbanizeAPICall $call) {
		$api_key = $this->api_key;
		
		$function = $call->function;
		$format = $call->format;
		
		$url = $this->kanbanize_url;
		$url .= "/$function";
		
		if ($format) {
			$url .= "/format/$format";
		}
		
		// # headers and data (this is API dependent, some uses XML)
		$headers = $call->headers; // array('Accept: application/json');
		$data = $call->data; // array('firstName' => 'John', 'lastName' => 'Doe');
		
		$handle = curl_init ();
		curl_setopt ( $handle, CURLOPT_URL, $url );
		
		curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false );
		
		curl_setopt ( $handle, CURLOPT_POST, true );
		curl_setopt ( $handle, CURLOPT_POSTFIELDS, json_encode ( $data ) );
		
		$headers [] = "apikey: $api_key";
		$headers [] = "Content-type: application/json; charset=utf-8";
		
		curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );
		
		$response = curl_exec ( $handle );
		
		$call->request_error = curl_error ( $handle );
		
		$call->response = $response;
		
		$call->response_code = ( int ) curl_getinfo ( $handle, CURLINFO_HTTP_CODE );
		
		curl_close ( $handle );
		return $call;
	}
	protected function doCall(KanbanizeAPICall $call) {
		$call = $this->executeCall ( $call );
		if ($call->request_error) {
			throw new KanbanizeApiException(  'problem with call: ' . $call->request_error );
		}
		
		return $call->getResponseDecoded ();
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board where the task to be moved is located. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int $taskid
	 *        	The ID of the task to be deleted.
	 * @param string $column
	 *        	The name of the column to move the task into. If the name of the column is unique, you can specify it alone, but if there are more than one columns with that name, you must specify it as columnname1 . columnname2 . columnname3
	 *        	
	 * @param array $options
	 *        	- lane The name of the swim-lane to move the task into. If omitted, the swimlane doesn't change
	 *        	- position The position of the task in the new column (zero-based). If omitted, the task will be placed at the bottom of the column
	 *        	- exceedingreason If you can exceed a limit with a reason, supply it with this parameter
	 *        	
	 *        	
	 * @return int The status of the operation (1 or error).
	 */
	public function moveTask($boardid, $taskid, $column, $options = array()) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'move_task' );
		
		$d = array (
				'boardid' => $boardid,
				'taskid' => $taskid,
				'column' => $column 
		);
		
		foreach ( $options as $k => $v ) {
			$d [$k] = $v;
		}
		
		$call->setData ( $d );
		
		$resp = $this->doCall ( $call );
		return $resp;
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int $taskid
	 *        	The ID of the task to be deleted.
	 *        	
	 * @param array $options
	 *        	- history	Set to "yes" if you want to get history for the task.
	 *        	- event	Only applicable if "history" is set to "yes". Accepts the following events: move, create, update, block, delete, comment, archived, subtask, loggedtime. If the parameter is not set, all of the events will be returned.
	 *        	- textformat	Options: "plain" (default) and "html". If the plain text format is used, the HTML tags are stripped from the task description.
	 *        	
	 * @return array - taskid	The ID of the task
	 *         - title	Title of the task
	 *         - description	Description of the task
	 *         - type	The task type
	 *         - assignee	Username of the assignee
	 *         - subtasks	Number of subtasks
	 *         - subtaskscomplete	Number of completed subtasks
	 *         - color	Task color
	 *         - priority	Task priority
	 *         - size	Task size
	 *         - deadline	Task deadline in format Day Month (e.g. 01 Aug)
	 *         - deadlineoriginalformat	Task deadline in format yyyy-mm-dd (e.g. 2012-08-01)
	 *         - extlink	Task external link
	 *         - tags	Task tags
	 *         - leadtime	Leadtime in days
	 *         - blocked	Is the task blocked (0 - no/ 1 - yes)
	 *         - blockedreason	Why the task is blocked
	 *         - subtaskdetails	Details of any subtasks (subtask id, subtask assignee, subtask title, subtask date of completion).
	 *         - historydetails	Details of task history (eventtype, historyevent, details, author, date, history id).
	 *         - columnname	The name of the column in which the task is located.
	 *         - lanename	The name of the swim-lane in which the task is located.
	 *         - columnid	The ID of the column in which the task is located.
	 *         - laneid	The ID of the swim-lane in which the task is located.
	 *         - columnpath	The full path to the card column in the format: "Column.Subcolumn1.Subcolumn2". If the task is located in a main column this will be the same as "columnname".
	 *         - loggedtime	The accumulated logged time of the task in hours.
	 *        
	 */
	public function getTaskDetails($boardid, $taskid, $options = array ()) {
		$call = new KanbanizeAPICall();
		$call->setFunction ( 'get_task_details' );
		
		$d = array (
				'boardid' => $boardid,
				'taskid' => $taskid 
		);
		
		foreach ( $options as $k => $v ) {
			$d [$k] = $v;
		}
		
		$call->setData ( $d );
		
		return $this->doCall ( $call );
	}
	
	/**
	 *
	 * @param string $email
	 *        	Your email address
	 * @param string $pass
	 *        	Your password
	 *        	
	 * @return array - email Your email address
	 *         - username Your username
	 *         - realname Your name
	 *         - companyname Company name
	 *         - timezone Your time zone
	 *         - apykey Your API key.
	 *        
	 */
	public function login($email, $pass) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'login' );
		$call->setData ( array (
				'email' => $email,
				'pass' => $pass 
		) );
		
		return $this->doCall ( $call );
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param array $data
	 *        	- title Title of the task
	 *        	- description Description of the task
	 *        	- priority One of the following: Low, Average, High
	 *        	- assignee Username of the assignee (must be a valid username)
	 *        	- color Any color code (e.g. 99b399) DO NOT PUT the # sign in front of the code!!!
	 *        	- size Size of the task
	 *        	- tags Space separated list of tags
	 *        	- deadline Dedline in the format: yyyy-mm-dd (e.g. 2011-12-13)
	 *        	- extlink A link in the following format: https:\\github.com\philsturgeon. If the parameter is embedded in the request BODY, use a standard link: https://github.com/philsturgeon.
	 *        	- type The name of the type you want to set.
	 *        	- template The name of the template you want to set. If you specify any property as part of the request, the one specified in the template will be overwritten.
	 *        	
	 * @return int null ID of the newly created task
	 */
	public function createNewTask($boardid, $data = array()) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'create_new_task' );
		
		$d = array ();
		foreach ( $data as $k => $v ) {
			$d [$k] = $v;
		}
		
		$d ['boardid'] = $boardid;
		
		$call->setData ( $d );
		
		$resp = $this->doCall ( $call );
		if ($resp) {
			return @$resp ['taskid'] ?  : @$resp ['id'] ?  : null;
		}
		
		return null;
	}
	
	/**
	 * get_board_settings method.
	 * Limit 30/hour
	 *
	 * @param int $boardid
	 *        	The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 *        	
	 *        	
	 * @return array - usernames Array containing the usernames of the board members.
	 *         - avatars	Associative array containing the URLs to the avatars of the board members.
	 *         - templates Array containing the templates available to this board.
	 *         - types Array containing the types available to this board.
	 */
	public function getBoardSettings($boardid) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'get_board_settings' );
		
		$call->setData ( array (
				'boardid' => $boardid 
		) );
		
		return $this->doCall ( $call );
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 *        	
	 * @return array - columns Array containing the board columns (only the columns on last level are returned)
	 *         - columns[][position] The position of the column
	 *         - columns[][lcname] The name of the column.
	 *         - columns[][description] The description of the column or swimlane.
	 *         - lanes Array containing the board swimnales.
	 *         - lanes[][lcname] The name of the swimlane.
	 *         - lanes[][color] The color of the swimlane.
	 *         - lanes[][description] The description of the column or swimlane.
	 */
	public function getBoardStructure($boardid) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'get_board_structure' );
		
		$call->setData ( array (
				'boardid' => $boardid 
		) );
		
		return $this->doCall ( $call );
	}
	
	/**
	 *
	 * @return array null array
	 *         - streams	Array of the streams.
	 *         - [][name] The name of the stream
	 *         - [][id] The ID of the stream
	 *         - [][boards] Array of details for any boards in current stream ( name, id )
	 *        
	 */
	public function getStreamsAndBoards() {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'get_streams_and_boards' );
		
		$resp = $this->doCall ( $call );
		if ($resp) {
			return @$resp ['streams'] ?  : null;
		}
		
		return null;
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int $taskid
	 *        	The ID of the task to be deleted.
	 *        	
	 *        	
	 * @return int The status of the operation (1 or error).
	 */
	public function deleteTask($boardid, $taskid) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'delete_task' );
		
		$call->setData ( array (
				'boardid' => $boardid,
				'taskid' => $taskid 
		) );
		
		$resp = $this->doCall ( $call );
		return $resp;
	}
	
	/**
	 *
	 * @param int $taskid
	 *        	The ID of the task you want to comment.
	 * @param string $comment
	 *        	The comment.
	 *        	
	 *        	
	 * @return array - id	ID of the history event
	 *         - author	Author of the comment
	 *         - date	Current date
	 */
	public function addComment($taskid, $comment) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'add_comment' );
		
		$d = array (
				'taskid' => $taskid,
				'comment' => $comment 
		);
		$call->setData ( $d );
		
		return $this->doCall ( $call );
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 *        	
	 * @param array $options
	 *        	- subtasks	Set to "yes" if you want to get subtask details for each task.
	 *        	- container	Set to "archive" if you want to get tasks from archive.
	 *        	- fromdate	Only applicable if container is set to "archive". The date after which the tasks of interest have been archived. Accepts the following formats: '2012-05-05', 'now', '10 September 2012', '-1 day', '-1 week 2 days', 'last Monday'. Default valuе is '1970-01-01'
	 *        	- todate	Only applicable if container is set to "archive". The date before which the tasks of interest have been archived. Accepts the following formats: '2012-05-05', 'now', '10 September 2012', '-1 day', '-1 week 2 days', 'last Monday'. Default valuе is 'now'
	 *        	- version	Gives the tasks from the specified archive version. The fromdate and todate parameters are ignored.
	 *        	- page	With this parameter you control which page number to get. The method returns 30 tasks per page.
	 *        	- textformat	Options: "plain" (default) and "html". If the plain text format is used, the HTML tags are stripped from the task description.
	 *        	
	 * @return array - taskid	The ID of the task
	 *         - position	The position of the task
	 *         - type	The task type
	 *         - assignee	Username of the assignee
	 *         - title	Title of the task
	 *         - description	Description of the task
	 *         - subtasks	Number of subtasks
	 *         - subtaskscomplete	Number of completed subtasks
	 *         - color	Task color
	 *         - priority	Task priority
	 *         - size	Task size
	 *         - deadline	Task deadline in format Day Month (e.g. 01 Aug)
	 *         - deadlineoriginalformat	Task deadline in format yyyy-mm-dd (e.g. 2012-08-01)
	 *         - extlink	Task external link
	 *         - tags	Task tags
	 *         - columnid	The ID of the column in which the task is located.
	 *         - laneid	The ID of the swim-lane in which the task is located.
	 *         - leadtime	Leadtime in days
	 *         - blocked	Is the task blocked (0 - no/ 1 - yes)
	 *         - blockedreason	Why the task is blocked
	 *         - subtaskdetails	Details of any subtasks (subtask id, subtask assignee, subtask title, subtask date of completion).
	 *         - columnname	The name of the column in which the task is located.
	 *         - lanename	The name of the swim-lane in which the task is located.
	 *         - columnpath	The full path to the card column in the format: "Column.Subcolumn1.Subcolumn2". If the task is located in a main column this will be the same as "columnname".
	 *         - loggedtime	The accumulated logged time of the task in hours.
	 *        
	 */
	public function getAllTasks($boardid, $options = array()) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'get_all_tasks' );
		
		$d = array (
				'boardid' => $boardid 
		);
		foreach ( $options as $k => $v ) {
			$d [$k] = $v;
		}
		
		$call->setData ( $d );
		
		return $this->doCall ( $call );
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board where the task to be moved is located. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int $taskid
	 *        	The ID of the task to be deleted.
	 * @param string $event
	 *        	Possible valules:
	 *        	- 'block' - block a task
	 *        	- 'editblock' - edit the blocked reason
	 *        	- 'unblock' - unblock a task
	 * @param string $blockreason
	 *        	Required if event is set to 'block' or 'editblock
	 *        	
	 * @return int The status of the operation (1 or error).
	 */
	public function blockTask($boardid, $taskid, $event, $blockreason = null) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'block_task' );
		
		$d = array (
				'boardid' => $boardid,
				'taskid' => $taskid,
				'event' => $event 
		);
		
		if ($blockreason) {
			$d ['blockreason'] = $blockreason;
		}
		
		$call->setData ( $d );
		
		$resp = $this->doCall ( $call );
		if ($resp) {
			return @$resp ['status'] ?  : null;
		}
		
		return null;
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board where the task to be moved is located. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int $taskid
	 *        	The ID of the task to be deleted.
	 *        	
	 * @param array $changeData
	 *        	- title Title of the task
	 *        	- description Description of the task
	 *        	- priority One of the following: Low, Average, High
	 *        	- assignee Username of the assignee (must be a valid username)
	 *        	- color Any color code (e.g. 99b399) DO NOT PUT the # sign in front of the code!!!
	 *        	- size Size of the task
	 *        	- tags Space separated list of tags
	 *        	- deadline Dedline in the format: yyyy-mm-dd (e.g. 2011-12-13)
	 *        	- extlink A link in the following format: https:\\github.com\philsturgeon. If the parameter is embedded in the request BODY, use a standard link: https://github.com/philsturgeon.
	 *        	- type The name of the type you want to set.
	 *        	
	 *        	
	 * @return int The status of the operation (1 or error).
	 */
	public function editTask($boardid, $taskid, $changeData = array()) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'edit_task' );
		
		$d = array (
				'boardid' => $boardid,
				'taskid' => $taskid 
		);
		
		foreach ( $changeData as $k => $v ) {
			$d [$k] = $v;
		}
		
		$call->setData ( $d );
		
		$resp = $this->doCall ( $call );
		if ($resp) {
			return @$resp ['status'] ?  : null;
		}
		
		return null;
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param string $from_date
	 *        	The date after which the activities of interest happened. Accepts the following formats: '2012-05-05', '10 September 2012'.
	 * @param string $to_date
	 *        	The date before which the activities of interest happened. Accepts the following formats: '2012-05-05', '10 September 2012'.
	 *        	
	 * @param array $options
	 *        	- page Default is 1
	 *        	- resultsperpage Default is 30
	 *        	- author Default is ALL
	 *        	- eventtype Options : Transitions, Updates, Comments, Blocks. Default is All
	 *        	- textformat Options: "plain" (default) and "html". If the plain text format is used, the HTML tags are stripped from the history details.
	 *        	
	 * @return array - allactivities	The number of all activities for the corresponding time window specified by the fromdate and todate parameters.
	 *         - page	The current page.
	 *         - activities	Array containing the board activities.
	 *         - activities[][author]	Who performed the action.
	 *         - activities[][event]	Type of the event (Task moved, Task blocked, Task archived etc.)
	 *         - activities[][text]	History details.
	 *         - activities[][date]	When the event happened.
	 *         - activities[][taskid]	The id of the task which was updated/moved/blocked, etc.
	 */
	public function getBoardActivities($boardid, $from_date, $to_date, $options = array()) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'get_board_activities' );
		$d = array (
				'boardid' => $boardid,
				'fromdate' => $from_date,
				'todate' => $to_date 
		);
		foreach ( $options as $k => $v ) {
			$d [$k] = $v;
		}
		
		$call->setData ( $d );
		
		return $this->doCall ( $call );
	}
	
	/**
	 *
	 * @param int $taskParent
	 *        	The ID of the task where to add the subtas
	 *        	
	 * @param array $options
	 *        	- title Title of the subtask
	 *        	- assignee username of the assignee(must be a valid username)
	 *        	
	 * @return int the id of the newly created subtask or 0 if an error occurred
	 */
	public function addSubtask($taskParent, $options = array()) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'add_subtask' );
		
		$d = array (
				'taskparent' => $taskParent 
		);
		foreach ( $options as $k => $v ) {
			$d [$k] = $v;
		}
		
		$call->setData ( $d );
		
		$resp = $this->doCall ( $call );
		return $resp;
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board whose structure you want to get. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 *        	
	 *        	
	 * @return array - columns	Array containing the board columns
	 *         - columns[][position]	The position of the column
	 *         - columns[][lcname]	The name of the column.
	 *         - columns[][path]	Unique identifier which contains the id of the column with all its parent columns. E.g. progress_17_1478 means that the column id is 1478, its parent is the column with id 17 and the parent area is IN PROGRESS.
	 *         - columns[][description]	The description of the column or swimlane.
	 *         - columns[][lcid]	Lane/Column ID. This is the ID of the column, which is the last part of the path parameter described above.
	 *         - columns[][children[]]*	If the column has sub-columns, they are returned in the children array recursively.
	 *         - lanes	Array containing the board swimnales.
	 *         - lanes[][position]	The position of the swimlane.
	 *         - lanes[][lcname]	The name of the swimlane.
	 *         - lanes[][path]	Unique identifier of the swimlane which concatenates the string "lane_" concatenated with the swimlane ID.
	 *         - lanes[][color]	The color of the swimlane.
	 *         - lanes[][description]	The description of the column or swimlane.
	 *         - lanes[][lcid]	Lane/Column ID. This is the ID of the swimlane, which is the last part of the path parameter described above.
	 */
	public function getFullBoardStructure($boardid) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'get_full_board_structure' );
		
		$call->setData ( array (
				'boardid' => $boardid 
		) );
		
		return $this->doCall ( $call );
	}
	
	/**
	 *
	 * @param int $boardid
	 *        	The ID of the board where the subtask to be edited is located. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int $subtaskid
	 *        	ID of the subtask
	 *        	
	 * @param array $options
	 *        	- title Title of the subtask
	 *        	- assignee username of the assignee(must be a valid username)
	 *        	- complete Options: 1 or 0. If it`s set to 1 the subtask will be marked as finished, otherwise as unfinished.
	 *        	
	 * @return int The status of the operation (1 or error).
	 */
	public function editSubtask($boardid, $subtaskid, $options = array()) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'edit_subtask' );
		$d = array (
				'boardid' => $boardid,
				'subtaskid' => $subtaskid 
		);
		foreach ( $options as $k => $v ) {
			$d [$k] = $v;
		}
		
		$call->setData ( $d );
		
		$resp = $this->doCall ( $call );
		return $resp;
	}
	
	/**
	 *
	 * @param int $loggedtime
	 *        	number of hours you want to add to the task.
	 * @param int $taskid
	 *        	ID of the task or subtask to log time to.
	 *        	
	 * @param array $options
	 *        	- description Comment about the log time entry.
	 *        	
	 * @return array - id	The id of the log time event.
	 *         - historyid	The id of the event (for internal use).
	 *         - taskid	The id of the task or subtask that has been updated with a log time event.
	 *         - author	The username of the API user who updated the task.
	 *         - details	Message explaining the log time event.
	 *         - loggedtime	The number of hours that have been logged.
	 *         - issubtask	A boolean parameter that shows whether the task is a subtask or not.
	 *         - title	The title of the task that has been updated.
	 *         - comment	The comment that has been added along with the time log.
	 *         - origindate	Timestamp of the event (no timezone applied ).
	 *         - entrydate	Timestamp of the event (timezone applied ).
	 */
	public function logTime($loggedtime, $taskid, $options = array()) {
		$call = new KanbanizeAPICall ();
		$call->setFunction ( 'log_time' );
		
		$d = array (
				'loggedtime' => $loggedtime,
				'taskid' => $taskid 
		);
		foreach ( $options as $k => $v ) {
			$d [$k] = $v;
		}
		
		$call->setData ( $d );
		
		$resp = $this->doCall ( $call );
		return $resp;
	}
}




