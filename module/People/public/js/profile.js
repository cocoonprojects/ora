var Profile = function(url) {

	this.taskStatsUrl = '';
	this.memberUrl = url.replace('profiles', 'people/members');

	this.bindEventsOn();
};

Profile.prototype = {

	constructor : Profile,
	classe : 'Profile',

	bindEventsOn: function() {
		var that = this;

		$("#taskFilters").on("submit", function(e) {
			e.preventDefault();
			that.loadTasks();
			that.loadTaskStats();
		});

		$("body").on("click", "a[data-action=nextPage]", function(e){
			e.preventDefault();
			var limit = $('#taskFilters input[name=limit]');
			limit.val(parseInt(limit.val()) + 10);
			that.loadTasks();
		});
	},

	loadUserDetail : function() {
		$.ajax({
			url : this.memberUrl,
			headers : {
				'GOOGLE-JWT' : sessionStorage.token
			},
			statusCode: {
				401: redirectToLogin
			},
			success: this.onUserDetailLoaded.bind(this)
		});
	},

	loadAccountStats : function (url) {
		$.ajax({
			url: url,
			headers : {
				'GOOGLE-JWT' : sessionStorage.token
			},
			statusCode: {
				401: redirectToLogin
			},
			success: this.onAccountStatsLoaded.bind(this)
		});
	},

	loadTasks: function() {
		$.ajax({
			url: $("#task-metrics form").attr('action'),
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			data: $("#task-metrics form").serialize(),
			statusCode: {
				401: redirectToLogin
			},
			success: this.onTasksLoaded.bind(this)
		});
	},

	loadTaskStats: function()
	{
		$.ajax({
			url: this.taskStatsUrl,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			data: $("#task-metrics form").serialize(),
			statusCode: {
				401: redirectToLogin
			},
			success: this.onTasksStatsLoaded.bind(this)
		});
	},

	onUserDetailLoaded : function(data) {
		$('#photo').attr('src', data.picture);
		$('#name').html(data.firstname + " " + data.lastname);
		$('#email').html(data.email);
		$('#birthday').html(data.birthday || "No Birthday Available");
		$('#description').html(data.description || "No User Profile Description Available");

		var memberSince = new Date(Date.parse(data.createdAt));
		$('#orgMembership').html(data.role + " since " + memberSince.toLocaleDateString());

		this.loadAccountStats(data._links['ora:account'].href);

		this.taskStatsUrl = data._links['ora:member-stats'].href;
		this.loadTaskStats();
	},

	onAccountStatsLoaded : function(data) {
		$('#tdTotal').html(data.total);
		$('#tdAvailable').html(data.balance);
		$('#tdLast3Month').html(data.last3M);
		$('#tdLast6Month').html(data.last6M);
		$('#tdLastYear').html(data.last1Y);
	},

	onTasksLoaded: function(data) {
		var memberId = $("#task-metrics input[name=memberId]").val();
		var taskUtils = new TaskUtils();

		var container = $('#task-metrics tbody');
		container.empty();
		$.each(data._embedded['ora:task'], function(key, task) {
			var member = task.members[memberId];
			container.append(
				'<tr data-id="' + task.id + '">' +
					'<td style="text-align: center">' + (member.role == 'owner' ? '<i class="mdi-action-grade" title="owner"></i>' : '') + '</td>' +
					'<td><a href="#" data-href="' + task._links.self.href + '" data-toggle="modal" data-target="#taskDetailModal">' + task.subject + '</a></td>' +
					'<td style="text-align: center">' + taskUtils.statuses[task.status] + '</td>' +
					'<td style="text-align: right">' + (member.credits || '') + '</td>' +
					'<td style="text-align: right">' + (member.delta || '') + '</td>' +
				'</tr>');
		});
		if(data.total == 0){
			container.append('<tr><td colspan="5">No tasks match the filter criteria</td></tr>');
		}
		if(data._links.next) {
			container.append(
				'<tr><td colspan="5" style="text-align: center"><a rel="next" href="#" data-action="nextPage">More</a></td></tr>'
			);
		}
	},

	onTasksStatsLoaded: function(data) {
		var container = $('#task-metrics tfoot');
		container.empty();
		container.append(
			'<tr>' +
			'<td style="text-align: center">' + data.ownershipsCount + '</td>' +
			'<td colspan="2">' + data.membershipsCount + '</td>' +
			'<td style="text-align: right">' + data.creditsCount + '</td>' +
			'<td style="text-align: right">AVG: '+ (data.averageDelta * 100).toFixed(2) + '%</td>' +
			'</tr>');
	}

};

$().ready(function(e) {
	var googleID = sessionStorage.googleid;
	$('head').append( '<meta name="google-signin-client_id" content="'+googleID+'">' );
	var profile = new Profile(window.location.pathname);
	profile.loadUserDetail();
	profile.loadTasks();
});