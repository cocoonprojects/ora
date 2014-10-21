var TaskManagement = function()
{
	this.bindEventsOn(); 
};

TaskManagement.prototype = {
	
	constructor: TaskManagement,
	classe: 'TaskManagement',
	
	bindEventsOn: function()
	{
		// Events must be here
		listAvailableTask
		$('body').on('click', '#listAvailableTask', function(e){
			e.preventDefault();
			taskManagement.listAvailableTask()
		});
		
		$('body').on('click', '#btnCreateNewTask', function(e){
			e.preventDefault();
			taskManagement.createNewTask()
		});
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
						"<button id='btnCreateNewTask' type='button' class='btn btn-info btn-block'>Create a new task</button>" +
					"</form>"
			);
		
		if ($(json.tasks).length > 0)
		{
			$.each(json.tasks, function(key, task) {
				
				var actions = "";
				if (task.status == 20)
				{
					task.status = "Ongoing";
					actions = "<button class='btn btn-success btn-block'>Join & estimate</button>";
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