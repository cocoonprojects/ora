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
			that.openEditTaskBox(e);
		});
		
		// EDIT TASK
		$("body").on("submit", "#formEditTask", function(e){
			e.preventDefault();
			that.editTask(e);
		});
		
		// DELETE TASK
		$("body").on("click", "button[data-action='deleteTask']", function(e){
			e.preventDefault();
			that.deleteTask(e);
		});
		
		// JOIN TASK MEMBERS
		$("body").on("click", "button[data-action='joinTask']", function(e){
			e.preventDefault();
			that.joinTaskMembers(e);
		});
		
		// UNJOIN TASK MEMBERS
		$("body").on("click", "button[data-action='unjoinTask']", function(e){
			e.preventDefault();
			that.unjoinTaskMembers(e);
		});
	},
	
	unjoinTaskMembers: function(e)
	{
		var taskID = $(e.target).closest("tr").data("taskid");
		var userID = $(e.target).closest("tr").data("userid");
		
		$.ajax({
			url: basePath + '/task-management/tasks/' + taskID + '/members/' + userID,
			method: 'DELETE',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 200)
					alert("Succesfully unjoined from the Team of this task");
				else if (xhr.status === 403)
					alert("Error. You are not part of that team or the creator of this task");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
	joinTaskMembers: function(e)
	{
		var taskID = $(e.target).closest("tr").data("taskid");
		var userID = $(e.target).closest("tr").data("userid");
		
		$.ajax({
			url: basePath + '/task-management/tasks/' + taskID + '/members/' + userID,
			method: 'POST',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 201)
					alert("Succesfully join into the Team of this task");
				else if (xhr.status === 403)
					alert("Error. You are already part of that team or the creator of this task");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
	openEditTaskBox: function(e)
	{
		var taskID = $(e.target).closest("tr").data("taskid");
		var taskSubject = $(e.target).closest("tr").data("tasksubject");

		$("#editTaskBox").show();
		$("#inputEditTaskID").val(taskID);
		$('#inputEditTaskSubject').val(taskSubject);
		
	},
	
	editTask: function(e)
	{
		var taskID = $("#inputEditTaskID").val();

		$.ajax({
			url: basePath + '/task-management/tasks/' + taskID,
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
	
	deleteTask: function(e)
	{
		var taskID = $(e.target).closest("tr").data("taskid");
		
		if (confirm('Are you sure you want to delete this entity?'))
			this.deleteTaskConfirmed(taskID);
		else
			alert("Operation aborted...");
	},
	
	deleteTaskConfirmed: function(taskID)
	{
		$.ajax({
			url: basePath + '/task-management/tasks/' + taskID,
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
			url: basePath + '/task-management/tasks',
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
						"<input type='text' name='projectID' class='form-control' value='1' readonly/>" +
						"<div class='form-group'><label for='subject' style='font-weight:normal'>Subject</label><input type=text name='subject' class='form-control' value='' required></div>" +
						"<button type='submit' class='btn btn-info btn-block'>Create a new task</button>" +
					"</form>"
			);
		
		container
			.append("<div id='editTaskBox' style='display:none'>" +
						"<h2>Edit existing Task</h2>" +
						"<form id='formEditTask'>" +
							"<div class='form-group'><label for='taskID' style='font-weight:normal'>Task ID</label><input id='inputEditTaskID' type=text class='form-control' disabled></div>" +
							"<div class='form-group'><label for='subject' style='font-weight:normal'>Subject</label><input id='inputEditTaskSubject' type=text name='subject' class='form-control' required></div>" +
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
					
					// Stabilisco se visualizzare il JOIN oppure l'UNJOIN
					if (task.alreadyMember) {
						actions = actions + "<button data-action='unjoinTask' class='btn btn-danger' style='margin-left:5px;'>UnJoin</button>";
					} else {
						actions = actions + "<button data-action='joinTask' class='btn btn-success'>Join</button>";
					}
					
					actions = actions + "<button data-action='openEditTaskBox' class='btn btn-warning' style='margin-left:5px;margin-right:5px;'>Edit</button>";
					actions = actions + "<button data-action='deleteTask' class='btn btn-danger'>Delete</button>";
				}
				else if (task.status == 40)
				{
					task.status = "Accepted";
					actions = "<button class='btn btn-info btn-block'>Assign share</button>";
				}
							
				$('#listAvailableTasks tbody')
					.append(
						"<tr data-taskid='"+task.id+"' data-tasksubject='" + task.subject + "' data-userid='"+json.loggeduser.id+"'>" +
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
			url: basePath + '/task-management/tasks',
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