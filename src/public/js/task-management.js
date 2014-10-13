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
								"<th>ID</th>" +
								"<th>Subject</th>" +
								"<th>Created At</th>" +
								"<th>Created By</th>" +
							"</tr>" +
						"</thead>" +
						"<tbody></tbody>" +
					"</table>");
		
		container
			.append("<h2>Create new task</h2>" +
					"<form id='formCreateNewTask'>" +
						"<div class='form-group'><label for='projectID' style='font-weight:normal'>Project ID (manual for now)</label><input type=text name='projectID' class='form-control' value='' required></div>" +
						"<div class='form-group'><label for='subject' style='font-weight:normal'>Subject</label><input type=text name='subject' class='form-control' value='' required></div>" +
						"<button id='btnCreateNewTask' type='button' class='btn btn-info btn-block'>Create a new task</button>" +
					"</form>"
			);
		
		$.each(json.tasks, function(key, task) {
			$('#listAvailableTasks tbody')
				.append("<tr>" +
							"<td>" + task.ID + "</td>" +
							"<td>" + task.subject + "</td>" +
							"<td>" + task.createdAt.date + "</td>" +
							"<td>" + task.createdBy + "</td>" +
						"</tr>");
		});
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