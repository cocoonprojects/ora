var TaskDetail = function(taskUtils){
	
	this.utils = taskUtils;

	this.getLabelForAssignShares = function(daysLeft){

		if(daysLeft !== null){
			if(daysLeft == 1){
				return ": "+daysLeft + " day left";
			}else if(daysLeft == 0){
				return ": less than a day";
			}
			return ": "+daysLeft + " days left";
		}
		return "";
	};
	
	this.bindEventsOn();
};

TaskDetail.prototype = {

	constructor: TaskDetail,
	classe: 'TaskDetail',

	bindEventsOn : function(){
		var that = this;

		$("#taskDetailModal").on("show.bs.modal", function(e) {
			var container = $('#taskDetailModal h4');
			container.empty();
			container = $('#taskDetailModal .modal-body');
			container.empty();

			that.getTask(e);
		});
	},
		
	getTask : function(e){
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

	onTaskCompleted : function(json) {
		var container = $('#taskDetailModal h4');
		container.text(json.subject);
		container = $('#taskDetailModal .modal-body');
		container.append(this.renderTaskDetail(json));
	},

	renderTaskDetail : function(task){

		var that = this;
		var estimation = null;
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

		var createdAt = new Date(Date.parse(task.createdAt));

		var rv = '<ul class="task-details">' +
				'<li>' + task.stream.subject + '</li>' +
				'<li>Created at ' + createdAt.toLocaleString() + '</li>';

		if(task.acceptedAt){
			acceptedAt = new Date(Date.parse(task.acceptedAt));
			rv += '<li>Accepted at ' + acceptedAt.toLocaleString() + '</li>';
		}

		rv += '<li>' + this.utils.statuses[task.status];
		
		if(task.status == this.utils.TASK_STATUS.get('ACCEPTED')){
			rv += this.getLabelForAssignShares(task.daysRemainingToAssignShares);
		}

		rv += '</li>' + estimation + '</ul>';

		rv += '<table class="table table-striped"><caption>Members</caption>' +
				'<thead><tr><th style="width: 3em"></th><th></th><th style="text-align: right">Estimate</th>';
		//$.map(task.members, function(member, memberId) {
		//	rv += '<th style="text-align: center">' + member.firstname.charAt(0) + member.lastname.charAt(0) + '</th>';
		//});
		rv += '<th style="text-align: center">Avg</th><th style="text-align: center">&Delta;</th></tr></thead><tbody>';
		$.map(task.members, function(member, memberId) {
			var isOwner = that.utils.isTaskOwner(member.role);
			rv += "<tr>";
			rv += isOwner ? "<td class=\"text-center\"><i class=\"mdi-action-grade\" title=\"owner\"></i></td>" : "<td></td>";
			rv += '<th><img src="' + member.picture + '" style="max-width: 16px; max-height: 16px;" class="img-circle"> ' + member.firstname + ' ' + member.lastname + '</th>';
			rv += '<td style="text-align: right">'
			switch(member.estimation) {
				case undefined:
				case null:
					break;
				case -2 :
					rv += '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
					break;
				case -1 :
					rv += 'Skipped';
					break;
				default :
					rv += member.estimation.toString();
			}
			rv += '</td>';
			//$.map(task.members, function(m) {
			//	rv += '<td style="text-align: center">';
			//	if(m.shares != undefined) {
			//		rv += m.shares[memberId].value != null ? (m.shares[memberId].value * 100).toFixed(2) + '%' : 'Skipped';
			//	}
			//	rv += '</td>';
			//});
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
	}
}