var TaskUtils = function(){

	this.statuses = {
		0: 'Idea',
		10: 'Open',
		20: 'Ongoing',
		30: 'Completed',
		40: 'Shares assignment in progress',
		50: 'Closed'
	};

	this.TASK_STATUS = function() {

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
	}();

	var task_members_roles = {
		ROLE_OWNER : "OWNER",
		ROLE_MEMBER : "MEMBER"
	};
	
	this.isTaskOwner = function(taskMemberRole){
		return taskMemberRole.toUpperCase() == task_members_roles.ROLE_OWNER;
	}
};

var taskUtils = new TaskUtils();
