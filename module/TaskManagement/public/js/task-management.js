var TaskManagement = function()
{
	this.bindEventsOn();
};

TaskManagement.prototype = {

	constructor: TaskManagement,
	classe: 'TaskManagement',
	
	statuses: {
			0: 'Idea',
			10: 'Open',
			20: 'Ongoing',
			30: 'Completed',
			40: 'Shares assignment in progress',
			50: 'Closed'
	},
	
	data: [],
	
	streamsData: [],
	
	bindEventsOn: function()
	{
		var that = this;
		
		$("#createTaskModal").on("show.bs.modal", function(e) {
			var form = $(this).find("form");
			form[0].reset();
			$(this).find('div.alert').hide();
			
			var select = form.find("#createTaskStreamID");
			select.empty();
			select.append('<option></option>');
			$.each(that.streamsData._embedded['ora:stream'], function(i, object) {
				select.append('<option value="' + object.id + '">' + object.subject + '</option>');
			});
		});
		
		$("#createTaskModal").on("submit", "form", function(e){
			e.preventDefault();
			that.createNewTask(e);
		});

		$("#createStreamModal").on("show.bs.modal", function(e) {
			var f = $(this).find("form");
			f[0].reset();
			$(this).find('div.alert').hide();
			$("#createStreamModal :input:text:enabled:first").focus()
		});

		$("#createTaskModal").on("shown.bs.modal", function(e) {
			$("#createTaskModal :input:text:enabled:first").focus()
		});
		
		$("#createStreamModal").on("submit", "form", function(e){
			e.preventDefault();
			that.createNewStream(e);
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
		$("body").on("click", "a[data-action='acceptTask']", function(e){
			e.preventDefault();
			that.acceptTask(e);
		});

		$("body").on("click", "a[data-action='completeTask']", function(e){
			e.preventDefault();
			var button = $(e.target) // Button that triggered the modal
			var key = button.data("task");
			if(that.data._embedded['ora:task'][key].status == 40) {
				if(!confirm('Are you sure? Shares will be erased')) {
					return false;
				}
			}
			that.completeTask(e);
		});

		//BACK TO ONGOING
		$("body").on("click", "a[data-action='executeTask']", function(e){
			e.preventDefault();
			that.executeTask(e);
		});

		$("#estimateTaskModal").on("click", "button[data-action='skipEstimateTask']", function(e) {
			e.preventDefault();
			if(confirm('You aren\'t estimating the task, are you sure? This overwrite any previous estimation')) {
				that.skipEstimateTask(e);
			}
		});
		
		$("#estimateTaskModal").on("show.bs.modal", function(e) {
			var modal = $(this);
			modal.find('div.alert').hide();

			var button = $(e.relatedTarget);
			var url = button.data('href'); // Button that triggered the modal
			
			var form = modal.find("form").first();
			form.attr("action", url);
			
			var credits = button.data('credits');
			if(credits == undefined || credits == -1) {
				$('#estimateTaskCredits').val(null);
			} else {
				$('#estimateTaskCredits').val(credits);
			}
		});
		
		//INSERT ESTIMATION
		$("#estimateTaskModal").on("submit", "form", function(e){
			e.preventDefault();
			that.estimateTask(e);
		});

		$("#assignSharesModal").on("click", "button[data-action='skipAssignShares']", function(e) {
			e.preventDefault();
			if(confirm('You aren\'t assigning your shares to members, are you sure? This overwrite any previous share')) {
				that.skipAssignShares(e);
			}
		});
		
		$("#assignSharesModal").on("show.bs.modal", function(e) {
			var modal = $(this);
			modal.find('div.alert').hide();

			var button = $(e.relatedTarget) // Button that triggered the modal
			var url = button.data('href');
			
			var form = modal.find("form").first();
			form.attr("action", url);
			
			var container = $('#assignSharesMembers');
			container.empty();

			var key = button.data("task");
			$.each(that.data._embedded['ora:task'][key].members, function(i, object) {
				container.append('<div class="form-group">'
						+ '<label for="' + i + '" class="col-sm-4 control-label"><img src="' + object.picture + '" style="max-width: 16px; max-height: 16px;" class="img-circle"> ' + object.firstname + ' ' + object.lastname + '</label>'
						+ '<div class="col-sm-8">'
						+ '<input type="number" class="form-control" id="' + i + '" name="' + i + '" min="0" max="100">'
						+ '</div>'	
						+ '</div>');
			});
		});

		$("#assignSharesModal").on("submit", "form", function(e){
			e.preventDefault();
			that.assignShares(e);
		});

		$("#taskDetailModal").on("show.bs.modal", function(e) {
			container = $('#taskDetailModal h4');
			container.empty();
			container = $('#taskDetailModal .modal-body');
			container.empty();

			that.getTask(e);
		});

	},
	
	unjoinTask: function(e)
	{
		var url = $(e.target).attr('href');
		
		var that = this;
		
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
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
		
		var that = this;
		
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
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
		var url = $(e.relatedTarget).data('href');
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'GET'
		})
		.done(this.onTaskCompleted.bind(this));
		
	},
	
	onTaskCompleted: function(json) {
		var container = $('#taskDetailModal h4');
		container.text(json.subject);
		
		container = $('#taskDetailModal .modal-body');
		container.append(this.renderTaskDetail(json));
	},
	
	editTask: function(e)
	{
		var form = $(e.target);
		var url = form.attr('action');

		var that = this;
		
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
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
			
		var that = this;
		
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
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
		var url = $(e.target).attr('href');
		
		var that = this;
		
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
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
		var url = $(e.target).attr('href');
		
		var that = this;
		
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
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
		var url = $(e.target).attr('href');
		
		var that = this;
		
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
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
		that = this;
		$.ajax({
			url: 'task-management/tasks',
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'GET'
		}).done(that.onListTasksCompleted.bind(this));
	},
	
	updateStreams: function()
	{
		that = this;
		$.ajax({
			url: 'task-management/streams',
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'GET',
			success: function(data) {
				that.streamsData = data;
			}
		});
	},
	
	onListTasksCompleted: function(json)
	{
		this.data = json;
		if(this.data._links !== undefined && this.data._links['ora:create'] !== undefined) {
			$("#createTaskModal form").attr("action", this.data._links['ora:create']['href']);
			$("#createTaskBtn").show();
		} else {
			$("#createTaskModal form").attr("action", null);
			$("#createTaskBtn").hide();
		}

		var container = $('#tasks');
		container.empty();
		
		if ($(this.data._embedded['ora:task']).length == 0) {
			container.append("<p>No available tasks found</p>");
		} else {
			var that = this;
			$.each(this.data._embedded['ora:task'], function(key, task) {
				subject = task._links.self == undefined ? task.subject : '<a data-href="' + task._links.self.href + '" data-toggle="modal" data-target="#taskDetailModal">' + task.subject + '</a>';

				var actions = [];
				if (task._links['ora:complete'] != undefined) {
					label = task.status > TASK_STATUS.get('COMPLETED') ? "Revert to complete" : 'Complete';
					actions.push('<a href="' + task._links['ora:complete'] + '" data-task="' + key + '" data-action="completeTask" class="btn btn-default">' + label + '</a>');
				}
				if (task._links['ora:accept'] != undefined) {
					label = task.status > TASK_STATUS.get('ACCEPTED') ? 'Revert to accepted' : 'Accept';
					actions.push('<a href="' + task._links['ora:accept'] + '" data-action="acceptTask" class="btn btn-default">' + label + '</a>');
				}
				if (task._links['ora:execute'] != undefined) {
					label = task.status > TASK_STATUS.get('ONGOING') ? 'Revert to ongoing' : 'Start';
					actions.push('<a href="' + task._links['ora:execute'] + '" data-action="executeTask" class="btn btn-default">' + label + '</a>');
				}
				if (task._links['ora:estimate'] != undefined) {
					$e = '';
					for(var memberId in task.members) {
						var info = task.members[memberId];
						if(info.estimation != undefined && info.estimation.value != -2) {
							$e = ' data-credits="' + info.estimation.value + '"';
						}
					};
					actions.push('<a data-href="' + task._links['ora:estimate']	 + '"' + $e + ' data-toggle="modal" data-target="#estimateTaskModal" class="btn btn-default">Estimate</a>');
				}
				if (task._links['ora:join'] != undefined) {
					actions.push('<a href="' + task._links['ora:join'] + '" class="btn btn-default" data-action="joinTask">Join</a>');
				}
				if (task._links['ora:unjoin'] != undefined) {
					actions.push('<a href="' + task._links['ora:unjoin'] + '" data-action="unjoinTask" class="btn btn-default">Unjoin</a>');
				}
				if (task._links['ora:assignShares'] != undefined) {
					actions.push('<a data-href="' + task._links['ora:assignShares'] + '" data-task="' + key + '" data-toggle="modal" data-target="#assignSharesModal" class="btn btn-default">Assign share</a>');
				}
				if (task._links['ora:edit'] != undefined) {
					actions.push('<a data-href="' + task._links['ora:edit'] + '" data-subject="' + task.subject + '" data-toggle="modal" data-target="#editTaskModal" class="btn btn-default mdi-content-create"></a>');
				}
				if (task._links['ora:delete'] != undefined) {
					actions.push('<a href="' + task._links['ora:delete'] + '" data-action="deleteTask" class="btn btn-danger"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>');
				}
				
				rv = that.renderTask(task);
				if ( actions.length > 0 ) {
					rv += '<ul class="task-actions"><li>' + actions.join('</li><li>') + '</li></ul>';
				}

				container.append(
					'<li id= "'+task.id+'" class="panel panel-default">' +
						'<div class="panel-heading">' + subject + '</div>' +
						'<div class="panel-body">' + rv + '</div>' +
					'</li>');
			});
		}
	},
	
	renderTaskDetail : function(task) {
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
		
		createdAt = new Date(Date.parse(task.createdAt));

		rv = '<ul class="task-details">' +
				'<li>' + task.stream.subject + '</li>' +
				'<li>Created at ' + createdAt.toLocaleString() + '</li>';
		
		if(task.acceptedAt !== null){
			
			acceptedAt = new Date(Date.parse(task.acceptedAt));
			rv += '<li>Accepted at ' + acceptedAt.toLocaleString() + '</li>';
		}
		
		rv += '<li>' + this.statuses[task.status];
		
		if(task.status == TASK_STATUS.get('ACCEPTED')){
			rv += this.getLabelForAssignShares(task.daysRemainingToAssignShares);
		}
		
		rv += '</li>' + estimation + '</ul>';
		
		rv += '<table class="table table-striped"><caption>Members</caption>' +
				'<thead><tr><th></th><th style="text-align: right">Estimate</th>';
		$.map(task.members, function(member, memberId) {
			rv += '<th style="text-align: center">' + member.firstname.charAt(0) + member.lastname.charAt(0) + '</th>';
		});
		rv += '<th style="text-align: center">Avg</th><th style="text-align: center">&Delta;</th></tr></thead><tbody>';
		$.map(task.members, function(member, memberId) {
			rv += '<tr><th><img src="' + member.picture + '" style="max-width: 16px; max-height: 16px;" class="img-circle"> ' + member.firstname + ' ' + member.lastname + '</th>';
			rv += '<td style="text-align: right">'
			if(member.estimation != null) {
				switch(member.estimation.value) {
					case -2 : 
						rv += '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
						break;
					case -1 :
						rv += 'Skipped';
						break;
					default :
						rv += member.estimation.value.toString();
				}
			}
			rv += '</td>';
			$.map(task.members, function(m) {
				rv += '<td style="text-align: center">';
				if(m.shares != undefined) {
					rv += m.shares[memberId].value != null ? (m.shares[memberId].value * 100).toFixed(2) + '%' : 'Skipped';
				}
				rv += '</td>';
			});
			rv += '<td style="text-align: center">';
			if(member.share != undefined && member.share != null) {
				rv += (member.share * 100).toFixed(2) + '%';
			}
			rv += '</td><td style="text-align: center">'
			if(member.delta != undefined && member.delta != null) {
				rv += '' + (member.delta * 100).toFixed(2) + '%';
			}
			rv += '</td></tr>';
		});
				
		rv +=	'</tbody>' +
			'</table>';
		return rv;
	},

	renderTask : function(task) {
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
		
		createdAt = new Date(Date.parse(task.createdAt));

		rv = '<ul class="task-details">' + 
				'<li>Created at ' + createdAt.toLocaleString() + '</li>';
		
		if(task.acceptedAt !== null){
			
			acceptedAt = new Date(Date.parse(task.acceptedAt));
			rv += '<li>Accepted at ' + acceptedAt.toLocaleString() + '</li>';
		}
		
		rv += '<li>' + this.statuses[task.status];
		
		if(task.status == TASK_STATUS.get('ACCEPTED')){
			rv += this.getLabelForAssignShares(task.daysRemainingToAssignShares);
		}

		rv += '</li>' + estimation +
				'<li>Members:' +
					'<ul>' + $.map(task.members, function(object, key) {
							rv = '<li><span class="task-member">' + object.firstname + " " + object.lastname;
							if(object.estimation != null){
								rv += ' <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
							}
							if(object.share != undefined) {
								rv += ' share: ' + (object.share * 100).toFixed(2) + '%';
								if(object.delta != null) {
									rv += ' (' + (object.delta * 100).toFixed(2) + ')';
								}
							}
							return rv + '</span></li>';
						}).join('') +
					'</ul>' +
				'</li>' +
			'</ul>';
		return rv;
	},
	
	createNewTask: function(e)
	{
		var url = $(e.target).attr('action');

		var that = this;
		
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
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
	
	createNewStream: function(e)
	{
		var modal = $(e.delegateTarget);
		var form = $(e.target)

		var that = this;
		
		$.ajax({
			url: form.attr('action'),
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'POST',
			data: form.serialize(),
			success: function() {
				modal.modal('hide');
				that.updateStreams();
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show($('#createStreamModal'), 'danger', 'An unknown error "' + errorThrown + '" occurred while trying to create the stream');
			}
		});
	},
	
	skipEstimateTask : function (e){
		var modal = $(e.delegateTarget);
		var form = modal.find("form").first();

		var that = this;
		
		$.ajax({
			url: form.attr('action'),
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'POST',
			data: {value:-1},
			success: function() {
				modal.modal('hide');
				that.listTasks();
			},
			statusCode: {
				400 : function(jqHXR, textStatus, errorThrown){
					json = $.parseJSON(jqHXR.responseText);
					if(json.description != undefined) {
						that.show(modal, 'danger', json.description);
					}
					if(json.errors != undefined) {
						that.show(modal, 'danger', json.errors[0].message);
					}
				}
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show(m, 'danger', 'An unknown error "' + errorThrown + '" occurred while trying to skip the estimation');
			}
		});
	},
	
	estimateTask : function (e){
		var modal = $(e.delegateTarget);
		var form = $(e.target)

		var that = this;
		
		$.ajax({
			url: form.attr('action'),
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'POST',
			data: form.serialize(),
			success: function() {
				modal.modal('hide');
				that.listTasks();
			},
			statusCode: {
				400 : function(jqHXR, textStatus, errorThrown){
					json = $.parseJSON(jqHXR.responseText);
					if(json.description != undefined) {
						that.show(modal, 'danger', json.description);
					}
					if(json.errors != undefined) {
						that.show(modal, 'danger', json.errors[0].message);
					}
				}
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show(m, 'danger', 'An unknown error "' + errorThrown + '" occurred while trying to estimate the task');
			}
		});
	},
	
	skipAssignShares : function (e){
		var modal = $(e.delegateTarget);
		var form = modal.find("form").first();

		var that = this;
		
		$.ajax({
			url: form.attr('action'),
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'POST',
			data: {},
			success: function() {
				modal.modal('hide');
				that.listTasks();
			},
			statusCode: {
				400 : function(jqHXR, textStatus, errorThrown){
					json = $.parseJSON(jqHXR.responseText);
					if(json.description != undefined) {
						that.show(modal, 'danger', json.description);
					}
					if(json.errors != undefined) {
						that.show(modal, 'danger', json.errors[0].message);
					}
				}
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show(modal, 'danger', 'An unknown error "' + errorThrown + '" occurred while trying to assign shares');
			}
		});
	},
	
	assignShares : function (e){
		var modal = $(e.delegateTarget);
		var form = $(e.target)

		var that = this;
		
		$.ajax({
			url: form.attr('action'),
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'POST',
			data: form.serialize(),
			success: function() {
				modal.modal('hide');
				that.listTasks();
			},
			statusCode: {
				400 : function(jqHXR, textStatus, errorThrown){
					json = $.parseJSON(jqHXR.responseText);
					if(json.description != undefined) {
						that.show(modal, 'danger', json.description);
					}
					if(json.errors != undefined) {
						that.show(modal, 'danger', json.errors[0].message);
					}
				}
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show(modal, 'danger', 'An unknown error "' + errorThrown + '" occurred while trying to assign shares');
			}
		});
	},
	
	show: function(container, level, message) {
		alertDiv = container.find('div.alert');
		alertDiv.removeClass();
		alertDiv.addClass('alert alert-' + level);
		alertDiv.text(message);
		alertDiv.show();
	},
	
	getLabelForAssignShares: function(daysLeft){
		
		if(daysLeft !== null){
			if(daysLeft == 1){
				return ": "+daysLeft + " day left";
			}else if(daysLeft == 0){
				return ": less than a day";
			}
			return ": "+daysLeft + " days left";
		}
		return "";
	}
	
};

var TASK_STATUS = (function() {
	var labels = {
		0: 'Idea',
		10: 'Open',
		20: 'Ongoing',
		30: 'Completed',
		40: 'Shares assignment in progress',
		50: 'Closed'
	};
	
	var values = {
		'IDEA':			0,
		'OPEN':			10,
		'ONGOING':		20,
		'COMPLETED':	30,
		'ACCEPTED':		40,
		'CLOSED':		50
	}
	
	return {
		get: function(name) { return values[name]; },
		label: function(name) { return labels[name]; }
	};
})();

$().ready(function(e){
	$('#content div.alert').hide();
	$('#firstLevelMenu li').eq(0).addClass('active');
	collaboration = new TaskManagement();
	collaboration.listTasks();
	collaboration.updateStreams();
});