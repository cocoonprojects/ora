var TaskManagement = function()
{
	this.bindEventsOn(); 
};

TaskManagement.prototype = {
	
	constructor: TaskManagement,
	classe: 'TaskManagement',
	
	bindEventsOn: function()
	{
		var that = this;
		
		// LIST AVAILABLE TASKS
		$("body").on("click", "#listAvailableTask", function(e){
			e.preventDefault();
			that.listAvailableTask();
		});
		
		// CREATE NEW TASK
		$("body").on("submit", "#formCreateNewTask", function(e){
			e.preventDefault();
			that.createNewTask();
		});
		
		// OPEN EDIT TASK BOX
		$("body").on("click", "button[data-action='openEditTaskBox']", function(e){
			e.preventDefault();
			var taskID = $(e.target).data("taskid");
			that.openEditTaskBox(taskID);
		});
		
		// EDIT TASK
		$("body").on("submit", "#formEditTask", function(e){
			e.preventDefault();
			var taskID = $("#inputEditTaskID").val();
			that.editTask(taskID);
		});
		
		// DELETE TASK
		$("body").on("click", "button[data-action='deleteTask']", function(e){
			e.preventDefault();
			var taskID = $(e.target).data("taskid");
			that.deleteTask(taskID);
		});
		
		// JOIN TASK MEMBERS
		$("body").on("click", "button[data-action='joinTask']", function(e){
			e.preventDefault();
			var taskID = $(e.target).data("taskid");
			var userID = $(e.target).data("userid");
			that.joinTaskMembers(taskID, userID);
		});
	},
	
	joinTaskMembers: function(taskID, userID)
	{
		$.ajax({
			url: 'http://oraproject/task-management/tasks/' + taskID + '/members/' + userID,
			method: 'POST',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 201)
					alert("You joined into the Team of this task");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
	openEditTaskBox: function(taskID)
	{
		$("#editTaskBox").show();
		$("#inputEditTaskID").val(taskID);
		
	},
	
	editTask: function(taskID)
	{
		$.ajax({
			url: 'http://oraproject/task-management/tasks/' + taskID,
			method: 'PUT',
			data: $('#formEditTask').serialize(),
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 202)
					alert("Task edited succesfully");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
	deleteTask: function(taskID)
	{
		if (confirm('Are you sure you want to delete this entity?'))
			this.deleteTaskConfirmed(taskID);
		else
			alert("Operation aborted...");
	},
	
	deleteTaskConfirmed: function(taskID)
	{
		$.ajax({
			url: 'http://oraproject/task-management/tasks/' + taskID,
			method: 'DELETE',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 200)
					alert("Task deleted succesfully");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
	listAvailableTask: function()
	{
		$.ajax({
			url: 'http://oraproject/task-management/tasks',
			method: 'GET',
			dataType: 'json'
		})
		.done(this.onListAvailableTaskCompleted.bind(this));
	},
	
	onListAvailableTaskCompleted: function(json)
	{
		var container = $('.jumbotron').closest('.container');
		
		container
			.empty()
			.append("<h2>List of available tasks</h2>" +
					"<table id='listAvailableTasks' class='table table-striped table-bordered table-hover'>" +
						"<thead>" +
							"<tr class='success'>" +
								"<th>Subject</th>" +
								"<th>Created At</th>" +
								"<th>Created By</th>" +
								"<th>Members</th>" +
								"<th class='text-center'>Status</th>" +
								"<th class='text-center'>Actions</th>" +
							"</tr>" +
						"</thead>" +
						"<tbody></tbody>" +
					"</table>");
		
		container
			.append("<h2>Create new task</h2>" +
					"<form id='formCreateNewTask'>" +
						"<input type='hidden' name='projectID' class='form-control' value='1' />" +
						"<div class='form-group'><label for='subject' style='font-weight:normal'>Subject</label><input type=text name='subject' class='form-control' value='' required></div>" +
						"<button type='submit' class='btn btn-info btn-block'>Create a new task</button>" +
					"</form>"
			);
		
		container
			.append("<div id='editTaskBox' style='display:none'>" +
						"<h2>Edit existing Task</h2>" +
						"<form id='formEditTask'>" +
							"<div class='form-group'><label for='taskID' style='font-weight:normal'>Task ID</label><input id='inputEditTaskID' type=text class='form-control' disabled></div>" +
							"<div class='form-group'><label for='subject' style='font-weight:normal'>Subject</label><input type=text name='subject' class='form-control' required></div>" +
							"<button id='btnEditTask' type='submit' class='btn btn-warning btn-block'>Edit this task</button>" +
						"</form>"+
					"</div>"
		);
		
		$('#listAvailableTasks tbody').empty();
		
		if ($(json.tasks).length > 0)
		{
			$.each(json.tasks, function(key, task) {
				
				var actions = "";
				if (task.status == 20)
				{
					task.status = "Ongoing";
					actions = "<button data-action='joinTask' data-userid='"+json.loggeduser.id+"' data-taskid='"+task.id+"' class='btn btn-success'>Join</button><button data-action='openEditTaskBox' data-taskid='"+task.id+"' class='btn btn-warning' style='margin-left:5px;margin-right:5px;'>Edit</button><button data-action='deleteTask' data-taskid='"+task.id+"' class='btn btn-danger'>Delete</button>";
				}
				else if (task.status == 40)
				{
					task.status = "Accepted";
					actions = "<button class='btn btn-info btn-block'>Assign share</button>";
				}
							
				$('#listAvailableTasks tbody')
					.append(
						"<tr>" +
							"<td>" + task.subject + "</td>" +
							"<td>" + task.created_at.date.replace('.000000','') + "</td>" +
							"<td>" + task.created_by.name + "</td>" +
							"<td>" + task.members.join() + "</td>" +
							"<td class='text-center'>" + task.status + "</td>" +
							"<td class='text-center'>" + actions + "</td>" +
						"</tr>");
			});
		}
		else
			$('#listAvailableTasks tbody').append("<tr><td colspan='6'>No available tasks found</td></tr>");
	},
	
	createNewTask: function()
	{
		$.ajax({
			url: 'http://oraproject/task-management/tasks',
			method: 'POST',
			data: $('#formCreateNewTask').serialize(),
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 201)
					alert("New task created succesfully");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
};