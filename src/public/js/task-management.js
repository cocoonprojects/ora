var TaskManagement = function()
{
	this.bindEventsOn(); 
};

TaskManagement.prototype = {
	
	constructor: TaskManagement,
	classe: 'TaskManagement',
	
	bindEventsOn: function()
	{
		// LIST AVAILABLE TASKS
		$("body").on("click", "#listAvailableTask", function(e){
			e.preventDefault();
			taskManagement.listAvailableTask();
		});
		
		// CREATE NEW TASK
		$("body").on("submit", "#formCreateNewTask", function(e){
			e.preventDefault();
			taskManagement.createNewTask();
		});
		
		// OPEN EDIT TASK BOX
		$("body").on("click", "button[data-action='openEditTaskBox']", function(e){
			e.preventDefault();
			var taskID = $(e.target).data("taskid");
			taskManagement.openEditTaskBox(taskID);
		});
		
		// EDIT TASK
		$("body").on("submit", "#formEditTask", function(e){
			e.preventDefault();
			var taskID = $("#inputEditTaskID").val();
			taskManagement.editTask(taskID);
		});
		
		// REMOVE TASK
		$("body").on("click", "button[data-action='removeTask']", function(e){
			e.preventDefault();
			var taskID = $(e.target).data("taskid");
			taskManagement.removeTask(taskID);
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
			url: 'http://oraproject/task-management/task/' + taskID,
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
	
	removeTask: function(taskID)
	{
		alert("REMOVE TASK - TO BE CONTINUED...");
	},
	
	listAvailableTask: function()
	{
		$.ajax({
			url: 'http://oraproject/task-management/task',
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
						"<div class='form-group'><label for='projectID' style='font-weight:normal'>Project ID (Manual for now - Suggested: 1)</label><input type=text name='projectID' class='form-control' value='' required></div>" +
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
					actions = "<button class='btn btn-success'>Join & estimate</button><button data-action='openEditTaskBox' data-taskid='"+task.id+"' class='btn btn-warning' style='margin-left:5px;margin-right:5px;'>Edit</button><button data-action='removeTask' data-taskid='"+task.id+"' class='btn btn-danger'>Remove</button>";
				}
				else if (task.status == 40)
				{
					task.status = "Accepted";
					actions = "<button class='btn btn-info btn-block'>Assign share</button>";
				}
				
				var taskMembers = "";
				$.each(task.members, function(key, member) {
					taskMembers = taskMembers + member + ", ";
				});
				
				$('#listAvailableTasks tbody')
					.append(
						"<tr>" +
							"<td>" + task.subject + "</td>" +
							"<td>" + task.created_at.date + "</td>" +
							"<td>" + task.created_by + "</td>" +
							"<td>" + taskMembers + "</td>" +
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
			url: 'http://oraproject/task-management/task',
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