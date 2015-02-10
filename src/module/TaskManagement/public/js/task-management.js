var TaskManagement = function()
{
	this.bindEventsOn();
};

TaskManagement.prototype = {

	constructor: TaskManagement,
	classe: 'TaskManagement',
	
	statuses: {
			0: 'Idea',
			10:	'Open',
			20: 'Ongoing',
			30: 'Completed',
			40: 'Accepted'
	},
	
	data: [],
	
	bindEventsOn: function()
	{
		var that = this;
	        
		$("#createTaskModal").on("show.bs.modal", function(e) {
			form = $(this).find("form");
			form[0].reset();
			$(this).find('div.alert').hide();			
		});
		
		$("#createTaskModal").on("submit", "form", function(e){
			e.preventDefault();
			that.createNewTask(e);
		});
		
		$("#editTaskModal").on("show.bs.modal", function(e) {
			var button = $(e.relatedTarget) // Button that triggered the modal
			var url = button.data('href');
			$("#editTaskModal form").attr("action", url);
			var subject = button.data('subject');
			$('#editTaskSubject').val(subject);
			$(this).find('div.alert').hide();			
		});

		$("#editTaskModal").on("submit", "form", function(e){
			e.preventDefault();
			that.editTask(e);
		});
		
		// DELETE TASK
		$("body").on("click", "a[data-action='deleteTask']", function(e){
			e.preventDefault();
			that.deleteTask(e);
		});
		
		// JOIN TASK MEMBERS
		$("body").on("click", "a[data-action='joinTask']", function(e){
			e.preventDefault();
			that.joinTask(e);
		});
		
		// UNJOIN TASK MEMBERS
		$("body").on("click", "a[data-action='unjoinTask']", function(e){
			e.preventDefault();
			that.unjoinTask(e);
		});

        //ACCEPT TASK FOR KAMBANIZE       
		$("body").on("click", "button[data-action='acceptTask']", function(e){
			e.preventDefault();
			that.acceptTask(e);
		});

		$("body").on("click", "button[data-action='completeTask']", function(e){
			e.preventDefault();
			that.completeTask(e);
		});

        //BACK TO ONGOING             
		$("body").on("click", "button[data-action='executeTask']", function(e){
			e.preventDefault();
			that.executeTask(e);
		});

		$("body").on("click", "#estimateTaskSkip", function(e) {
			if($(this).prop('checked')) {
			    $("#estimateTaskCredits").prop('disabled', true);
			} else {
				$("#estimateTaskCredits").prop('disabled', false);
			}
		});

		$("#estimateTaskModal").on("show.bs.modal", function(e) {
			var button = $(e.relatedTarget) // Button that triggered the modal
			var url = button.data('href');
			$("#estimateTaskModal form").attr("action", url);
			var credits = button.data('credits');
			if(credits == undefined) {
				$('#estimateTaskCredits').val(null);
			    $("#estimateTaskCredits").prop('disabled', false);
			    $("#estimateTaskSkip").prop('checked', false);
			} else if (credits == -1) {
				$('#estimateTaskCredits').val(null);
				$("#estimateTaskCredits").prop('disabled', true);
				$("#estimateTaskSkip").prop('checked', true);
			} else {
				$('#estimateTaskCredits').val(credits);
			    $("#estimateTaskCredits").prop('disabled', false);
			    $("#estimateTaskSkip").prop('checked', false);
			}
			$(this).find('div.alert').hide();			
		});
		
		//INSERT ESTIMATION
		$("#estimateTaskModal").on("submit", "form", function(e){
			e.preventDefault();
			that.estimateTask(e);
		});

		$("#assignSharesModal").on("show.bs.modal", function(e) {
			var button = $(e.relatedTarget) // Button that triggered the modal
			var url = button.data('href');
			$("#assignSharesModal form").attr("action", url);
			
			var container = $('#assignSharesMembers');
			container.empty();

			var key = button.data("credits");
			$.each(that.data.tasks[key].members, function(i, object) {
				container.append('<div class="form-group">'
						+ '<label for="' + i + '" class="col-sm-4 control-label">' + object.firstname + ' ' + object.lastname + '</label>'
						+ '<div class="col-sm-8">'
						+ '<input type="number" class="form-control" id="' + i + '" name="' + i + '" min="0" max="100">'
						+ '</div>'	
						+ '</div>');
			});
			$(this).find('div.alert').hide();
		});

		$("#assignSharesModal").on("submit", "form", function(e){
			e.preventDefault();
			that.assignShares(e);
		});
	},
	
	unjoinTask: function(e)
	{
		var url = $(e.target).attr('href');
		
		that = this;
		
		$.ajax({
			url: url,
			method: 'DELETE',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				m = $('#content');
				if (xhr.status === 200) {
					that.show(m, 'success', 'You successfully left the team that is working on the task');
					that.listTasks();
				}
				else if (xhr.status === 204) {
					that.show(m, 'warning', 'You are not member of the team that is working on the task');
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to leave the task');
				}
			}
		});
	},
	
	joinTask: function(e)
	{
		var url = $(e.target).attr('href');
		
		that = this;
		
		$.ajax({
			url: url,
			method: 'POST',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				m = $('#content');
				if (xhr.status === 201) {
					that.show(m, 'success', 'You successfully joined the team that is working on the task');
					that.listTasks();
				}
				else if (xhr.status === 204) {
					that.show(m, 'warning', 'You are already member of the team that is working on the task');
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to join the task');
				}
			}
		});
	},
	
	getTask: function(e)
	{
		var url = $(e.relatedTarget).attr('href');
		$.ajax({
			url: url,
			method: 'GET',
			dataType: 'json'
		})
		.done(this.onTaskCompleted.bind(this));
		
	},
	
	onTaskCompleted: function(json) {
	},
	
	editTask: function(e)
	{
		var form = $(e.target);
		var url = form.attr('action');

		that = this;
		
		$.ajax({
			url: url,
			method: 'PUT',
			data: form.serialize(),
			dataType: 'json',
			complete: function(xhr, textStatus) {
				m = $('#editTaskModal');
				if (xhr.status === 202) {
					m.modal('hide');
					that.listTasks();
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to edit the task');
				}
			}
		});
	},
	
	deleteTask: function(e)
	{
		if (!confirm('Are you sure you want to delete this task?')) {
			return;
		}

		var url = $(e.target).attr('href');
			
		that = this;
		
		$.ajax({
			url: url,
			method: 'DELETE',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				m = $('#content');
				if (xhr.status === 200) {
					that.show(m, 'success', 'You successfully deleted the task');
					that.listTasks();
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to delete the task');
				}
			}
		});
	},
	
	acceptTask: function(e){
		var url = $(e.target).data('href');
		
		that = this;
		
        $.ajax({
            url: url,
            method: 'POST',
            data:{action:'accept'},
            dataType: 'json',
            complete: function(xhr, textStatus) {
				m = $('#content');
				if (xhr.status === 200) {
					that.show(m, 'success', 'You have successfully accepted the task');
					that.listTasks();
				}
				else if (xhr.status === 204) {
					that.show(m, 'warning', 'The task is already accepted');
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to acceot the task');
				}
            }
        });
    },

	completeTask: function(e){
		var url = $(e.target).data('href');
		
		that = this;
		
        $.ajax({
            url: url,
            method: 'POST',
            data:{action:'complete'},
            complete: function(xhr, textStatus) {
				m = $('#content');
				if (xhr.status === 200) {
					that.show(m, 'success', 'You have successfully completed the task');
					that.listTasks();
				}
				else if (xhr.status === 204) {
					that.show(m, 'warning', 'The task is already completed');
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to complete the task');
				}
            }
        });
    },

    executeTask: function(e){
		var url = $(e.target).data('href');
		
		that = this;
		
        $.ajax({
            url: url,
            method: 'POST',
            data:{action:'execute'},
            dataType: 'json',
            complete: function(xhr, textStatus) {
				m = $('#content');
				if (xhr.status === 200) {
					that.show(m, 'success', 'You have successfully put in execution the task');
					that.listTasks();
				}
				else if (xhr.status === 204) {
					that.show(m, 'warning', 'The task is already in execution');
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to execute the task');
				}
            }
        });
    },

    listTasks: function()
	{   
    	$('#content div.alert').hide();
    	
		$.ajax({
			url: '/task-management/tasks',
			method: 'GET',
			dataType: 'json'
		})
		.done(this.onListTasksCompleted.bind(this));
	},
	
	onListTasksCompleted: function(json)
	{
		this.data = json;
		
		if(this.data._links.create != undefined) {
			$("#createTaskModal form").attr("action", this.data._links.create);
			$("#createTaskBtn").show();
		} else {
			$("#createTaskModal form").attr("action", null);
			$("#createTaskBtn").hide();
		}

		var container = $('#tasks');
		container.empty();
		
		if ($(this.data.tasks).length == 0) {
			container.append("<p>No available tasks found</p>");
		} else {
			that = this;
			$.each(this.data.tasks, function(key, task) {
				subject = task._links.self == undefined ? task.subject : '<a href="' + task._links.self + '">' + task.subject + '</a>';
				createdAt = new Date(Date.parse(task.createdAt));
				var actions = [];
				if (task._links.complete != undefined) {
					actions.push('<button data-href="' + task._links.complete + '" data-action="completeTask" class="btn btn-default">Complete</button>');
				}
				if (task._links.accept != undefined) {
					actions.push('<button data-href="' + task._links.accept + '" data-action="acceptTask" class="btn btn-default">Accept</button>');
				}
				if (task._links.execute != undefined) {
					actions.push('<button data-href="' + task._links.execute + '" data-action="executeTask" class="btn btn-default">Ongoing</button>');
				}
				if (task._links.estimate != undefined) {
					$e = '';
					for(i = 0; i < task.members.length; i++) {
						if(task.members[i].estimation != undefined && task.members[i].estimation.value != -2) {
							$e = ' data-credits="' + task.members[i].estimation.value + '"';
						}
					};
					actions.push('<a data-href="' + task._links.estimate  + '"' + $e + ' data-toggle="modal" data-target="#estimateTaskModal" class="btn btn-default">Estimate</a>');
				}
				if (task._links.join != undefined) {
					actions.push('<a href="' + task._links.join + '" class="btn btn-default" data-action="joinTask">Join</a>');
				}
				if (task._links.unjoin != undefined) {
					actions.push('<a href="' + task._links.unjoin + '" data-action="unjoinTask" class="btn btn-default">Unjoin</a>');
				}
				if (task._links.assignShares != undefined) {
					actions.push('<a data-href="' + task._links.assignShares + '" data-credits="' + key + '" data-toggle="modal" data-target="#assignSharesModal" class="btn btn-default">Assign share</a>');
				}
				if (task._links.edit != undefined) {
					actions.push('<a data-href="' + task._links.edit + '" data-subject="' + task.subject + '" data-toggle="modal" data-target="#editTaskModal" class="btn btn-default mdi-content-create"></a>');
				}
				if (task._links['delete'] != undefined) {
					actions.push('<a href="' + task._links['delete'] + '" data-action="deleteTask" class="btn btn-danger"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>');
				}
				switch(task.estimation) {
				case undefined:
					estimation = '';
					break;
				case -1:
					estimation = '<li>Estimation skipped</li>';
					break;
				case null:
					estimation = '<li>Estimation in progress</li>';
					break;
				default:
					estimation = '<li>' + task.estimation + ' credits</li>';
				}
				
				a = actions.length == 0 ? '' : '<li>' + actions.join(' ') + '</li>';

				container.append(
                    '<li class="panel panel-default">' +
						'<div class="panel-heading">' + subject + '</div>' +
						'<div class="panel-body"><ul class="task-details"><li>Created at ' + createdAt.toLocaleString() + "</li>" +
						'<li>' + that.statuses[task.status] + '</li>' +
						estimation +
						"<li>Members: <ul>" + $.map(task.members, function(object, key) {
							rv = '<li><span class="task-member">' + object.firstname + " " + object.lastname;
							if(object.estimation != null){
								rv += ' <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
							}
							return rv + '</span></li>';
						}).join('') + "</ul></li>" + 
						a + '</div>' +
					'</li>');
			});
		}
	},
	
	createNewTask: function(e)
	{
		var url = $(e.target).attr('action');

		that = this;
		
		$.ajax({
			url: url,
			method: 'POST',
			data: $('#createTaskModal form').serialize(),
			dataType: 'json',
			complete: function(xhr, textStatus) {
				m = $('#createTaskModal');
				if (xhr.status === 201) {
					m.modal('hide');
					that.listTasks();
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to create the task');
				}
			}
		});
	},
	
	estimateTask : function (e){
		var url = $(e.target).attr('action');

		that = this;
		
		var credits = $('#estimateTaskSkip').is(':checked') ? -1 : $("#estimateTaskCredits").val();
				
		$.ajax({
			url: url,
			method: 'POST',
			data: {value:credits},
			dataType: 'json',
			complete: function(xhr, textStatus) {
				m = $('#estimateTaskModal');
				switch (xhr.status) {
				case 201:
					m.modal('hide');
					that.listTasks();
					break;
				case 400:
					that.show(m, 'danger', 'You have to estimate the task or skip it');
					break;
				default:
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to estimate the task');
				}
			}
		});
	},
	
	assignShares : function (e){
		var url = $(e.target).attr('action');

		m = $('#assignSharesModal');

		that = this;
		
		$.ajax({
			url: url,
			method: 'POST',
			data: $('#assignSharesModal form').serialize(),
			success: function() {
				m.modal('hide');
				that.listTasks();
			},
			statusCode: {
				400 : function(jqHXR, textStatus, errorThrown){
					json = $.parseJSON(jqHXR.responseText);
					if(json.description != undefined) {
						that.show(m, 'danger', json.description);
					}
					if(json.errors != undefined) {
						that.show(m, 'danger', json.errors[0].message);
					}
				}
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show(m, 'danger', 'An unknown error "' + errorThrown + '" occurred while trying to assign shares');
			}
		});
	},
	
	show: function(container, level, message) {
		alertDiv = container.find('div.alert');
		alertDiv.removeClass();
		alertDiv.addClass('alert alert-' + level);
		alertDiv.text(message);
		alertDiv.show();
	}
	
};

$().ready(function(e){
	$('#content div.alert').hide();
	$('#firstLevelMenu li').eq(0).addClass('active');
	collaboration = new TaskManagement();
	collaboration.listTasks();
});