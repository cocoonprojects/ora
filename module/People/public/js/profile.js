var Profile = function(taskUtils) {

	var tasksPageSize = 10,
		nextTasksPageSize = 10,
		endOn = "",
		startOn = "",
		userId = "",
		orgId = "";

	this.taskUtils = taskUtils;

	this.setTasksPageSize = function(size){
		tasksPageSize = size;
	};
	this.getTasksPageSize = function(){
		return tasksPageSize;
	};
	this.getNextTasksPageSize = function(){
		return nextTasksPageSize;
	};
	this.setStartOn = function(date){
		startOn = date;
	};
	this.getStartOn = function(){
		return startOn;
	};
	this.setEndOn = function(date){
		endOn = date;
	};
	this.getEndOn = function(){
		return endOn;
	};
	this.setUserId = function(id){
		userId = id;
	};
	this.getUserId = function(){
		return userId;
	};
	this.setOrgId = function(id){
		orgId = id;
	};
	this.getOrgId = function(){
		return orgId;
	};
	this.getStatsTaskUrl = function(){
		return "/"+this.getOrgId()+"/users/"+this.getUserId()+"/task-stats";
	}

	this.bindEventsOn();
};

Profile.prototype = {

	constructor : Profile,
	classe : 'Profile',
	data : [],

	bindEventsOn: function(){
		var that = this;

		$("#tasksFilter").on("click", "button", function(e){
			e.preventDefault();

			//TODO: da rivedere
			var start = $("#startOn").val() !== "" ? $("#startOn").val().split("/", 3) : "";
			var end = $("#endOn").val() !== "" ? $("#endOn").val().split("/", 3) : "";

			if(start.length == 3){
				that.setStartOn(start[2]+"-"+start[1]+"-"+start[0]);
			}else{
				that.setStartOn("");
			}
			if(end.length == 3){
				that.setEndOn(end[2]+"-"+end[1]+"-"+end[0]);
			}else{
				that.setEndOn("");
			}
			var url = "/"+profile.getOrgId()+"/task-management/tasks?endOn="+that.getEndOn()+"&startOn="+that.getStartOn()+"&memberId="+that.getUserId();
			that.getTaskMetrics(url);
		});

		$("body").on("click", "a[data-action='nextPage']", function(e){
			e.preventDefault();
			var url = $(e.target).attr('href');
			if(that.getEndOn()){
				url += "&endOn="+that.getEndOn();
			}
			if(that.getStartOn()){
				url += "&startOn="+that.getStartOn();
			}
			if(that.getUserId()){
				url += "&memberId="+that.getUserId();
			}
			that.getTaskMetrics(url);
		});
	},

	getTaskMetrics: function(listTaskUrl){
		var that = this;
		var listTasksRequest = this.listTasks(listTaskUrl);
		var taskStatsRequest = this.getTaskStats(this.getStatsTaskUrl());

		var listTaskResponse = listTasksRequest.then(function(json) {
			that.setTasksPageSize(json.count);
			return json;
		});
		var taskStatsResponse = taskStatsRequest.then(function(json) {
			return json;
		});
		$.when(listTaskResponse, taskStatsResponse)
		.done(function(listTasks, taskStats) {
			var container = that.createTaskMetricsTable(listTasks, taskStats);
			if(listTasks._links !== undefined && listTasks._links["next"] !== undefined) {
				var limit = that.getTasksPageSize() + that.getNextTasksPageSize();
				container.append(
					'<div class="text-center">' +
							'<a rel="next" href="'+listTasks._links["next"]["href"]+'?limit=' + limit + '" data-action="nextPage">More</a>' +
					'</div>'
				);
			}
		});
	},

	bindEventsOn: function(){
		var that = this;

		$("#tasksFilter").on("click", "button", function(e){
			e.preventDefault();

			//TODO: da rivedere
			var start = $("#startOn").val() !== "" ? $("#startOn").val().split("/", 3) : "";
			var end = $("#endOn").val() !== "" ? $("#endOn").val().split("/", 3) : "";

			if(start.length == 3){
				that.setStartOn(start[2]+"-"+start[1]+"-"+start[0]);
			}else{
				that.setStartOn("");
			}
			if(end.length == 3){
				that.setEndOn(end[2]+"-"+end[1]+"-"+end[0]);
			}else{
				that.setEndOn("");
			}
			var url = "/"+profile.getOrgId()+"/task-management/tasks?endOn="+that.getEndOn()+"&startOn="+that.getStartOn()+"&memberId="+that.getUserId();
			that.getTaskMetrics(url);
		});

		$("body").on("click", "a[data-action='nextPage']", function(e){
			e.preventDefault();
			var url = $(e.target).attr('href');
			if(that.getEndOn()){
				url += "&endOn="+that.getEndOn();
			}
			if(that.getStartOn()){
				url += "&startOn="+that.getStartOn();
			}
			if(that.getUserId()){
				url += "&memberId="+that.getUserId();
			}
			that.getTaskMetrics(url);
		});
	},

	loadUserDetail : function(url, redirectURL) {
		var that = this;

		var _url = window.location.protocol + "//" + window.location.host + url;

		$.ajax({
			url : _url,
			headers : {
				'GOOGLE-JWT' : sessionStorage.token
			},
			method : 'GET',
			data : {
			// id : userId
			}
		}).fail(function( jqXHR, textStatus ) {
			var errorCode = jqXHR.status;
			if(errorCode === 401){
				sessionStorage.setItem('redirectURL', redirectURL);
				window.location = '/';
			}
		}).done(that.onLoadUserProfileCompleted.bind(this));
	},

	onLoadUserProfileCompleted : function(json) {
		var container = $('#profile-content');

		$('#photo').attr('src', json.picture);
		$('#name').html(json.firstname + " " + json.lastname);
		$('#email').html(json.email);

		if (json.birthday == null) {
			$('#birthday').html("No Birthday Available");
		} else {
			$('#birthday').html(json.Birthday);// TODO Use this parameter's name
		}

		if (json.description == null) {
			$('#description').html("No User Profile Description Available");
		} else {
			$('#description').html(json.Birthday);// TODO Use this parameter's name
		}

		var membData = json._embedded['ora:organization-membership'];
		var memberSince = new Date(Date.parse(membData.createdAt));
		$('#orgName').html(membData.organization.name);
		$('#orgMembership').html(membData.role + " since " + memberSince.toLocaleDateString());
		
		var creditsData = json._embedded['credits'];
		//Generated credits Table
		$('#tdOrg').html(membData.organization.name);
		$('#tdTotal').html(creditsData.total);
		$('#tdAvailable').html(creditsData.balance);
		
		//Credit Account History
		$('#tdLast3Month').html(creditsData.last3M);
		$('#tdLast6Month').html(creditsData.last6M);
		$('#tdLastYear').html(creditsData.lastY);

		container.show();
	},

	listTasks: function(url){
		return $.ajax({
				url: url,
				headers: {
					'GOOGLE-JWT': sessionStorage.token
				},
				method: 'GET'
			}).fail(function( jqXHR, textStatus ) {
				var errorCode = jqXHR.status;
				var redirectURL = window.location.href;
				if(errorCode === 401){
					sessionStorage.setItem('redirectURL', redirectURL);
					window.location = '/';
				}
			});
	},

	createTaskMetricsTable(listTasks, taskStats){
		var tasks = listTasks._embedded['ora:task'];
		var container = $('#task-metrics');
		var that = this;
		var html = "<table class=\"table table-hover\">";
		html += "<thead>" +
					"<tr>" +
						"<th class=\"text-center\" style=\"width: 3em\"></th>" +
						"<th class=\"text-left\">Subject</th>" +
						"<th class=\"text-center\" style=\"width: 16em\">Status</th>" +
						"<th class=\"text-right\" style=\"width: 6em\">Credits</th>" +
						"<th class=\"text-right\" style=\"width: 8em\">&Delta; shares" +
					"</tr>" +
				"</thead>";
		html += "<tbody>";
		$.each(tasks, function(key, task) {
			html += "<tr data-id='"+task.id+"'>";
			var isOwner = false;
			var delta = null;
			var credits = null;
			$.each(task.members, function(memberId, info){
				if(memberId == that.getUserId()){
					isOwner = that.taskUtils.isTaskOwner(info.role);
					if(info.delta !== undefined && info.delta !== null){
						delta = (parseFloat(info.delta) * 100).toFixed(2) + " %";
					}
					if(info.credits !== undefined){
						credits = info.credits;
					}
				}
			});
			html += isOwner ? "<td class=\"text-center\"><i class=\"mdi-action-grade\" title=\"owner\"></i></td>" : "<td></td>";
			var subject = task._links.self == undefined ? task.subject : '<a style="cursor:pointer" data-href="' + task._links.self.href + '" data-toggle="modal" data-target="#taskDetailModal">' + task.subject + '</a>';
			html += "<td class=\"text-left\">"+subject+"</td>";
			html += "<td class=\"text-center\">"+that.taskUtils.statuses[task.status]+"</td>";
			html += credits !== null ? "<td class=\"text-right\">"+credits+"</td>" : "<td></td>";
			html += delta !== null ? "<td class=\"text-right\">"+delta+"</td>" : "<td></td>";
			html += "</tr>";
		});
		if($.isEmptyObject(tasks)){
			html += "<tr><td colspan=\"4\">No Tasks metrics available</td></tr>";
		}else{
			var ownershipsCount = taskStats.ownershipsCount;
			var membershipsCount = taskStats.membershipsCount;
			var creditsCount = taskStats.creditsCount;
			var averageDelta = taskStats.averageDelta;
			html += "<tr style=\"border-top: 2px solid darkgray;\">" +
						"<td class=\"text-center\">"+ownershipsCount+"</td>" +
						"<td class=\"text-left\">"+membershipsCount+"</td>" +
						"<td class=\"text-left\"></td>" +
						"<td class=\"text-right\">"+creditsCount+"</td>" +
						"<td class=\"text-right\">AVG:&nbsp&nbsp"+(averageDelta* 100).toFixed(2)+" %</td>";
			html += "</tr>";
		}
		html += "</tbody>" +
			"</table>";
		container.html(html);
		return container;
	},

	getTaskStats: function(url){
		return $.ajax({
				url: url,
				headers: {
					'GOOGLE-JWT': sessionStorage.token
				},
				method: 'GET'
			}).fail(function( jqXHR, textStatus ) {
				var errorCode = jqXHR.status;
				var redirectURL = window.location.href;
				if(errorCode === 401){
					sessionStorage.setItem('redirectURL', redirectURL);
					window.location = '/';
				}
			});
	}
};

$().ready(function(e) {
	var googleID = sessionStorage.googleid;
	$('head').append( '<meta name="google-signin-client_id" content="'+googleID+'">' );
	profile = new Profile(taskUtils);
	$('#profile-content').hide();
	var elem = document.getElementById("profile-content");
	profile.setOrgId(elem.getAttribute("org-id"));
	profile.setUserId(elem.getAttribute("user-id"));
	var url = "/"+profile.getOrgId()+"/user-profiles/"+profile.getUserId();
	var redirectURL = "/"+profile.getOrgId()+"/profiles/"+profile.getUserId();
	profile.loadUserDetail(url, redirectURL);
	var listTaskUrl = "/"+profile.getOrgId()+"/task-management/tasks?memberId="+profile.getUserId();
	profile.getTaskMetrics(listTaskUrl);
});