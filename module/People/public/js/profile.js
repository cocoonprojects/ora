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
			that.listTasks(url);
		});

		$("body").on("click", "a[data-action='nextPage']", function(e){
			e.preventDefault();
			that.listMoreTasks(e);
		});
	},

	loadUserDetail : function(url, redirectURL) {
		that = this;

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
		$('#orgName').html(membData.organization.name);
		$('#orgMembership').html(membData.role + " since " + membData.createdAt);
		
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

	listTasks: function(url)
	{
		var that = this;
		$.ajax({
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
		}).done(that.onListTasksCompleted.bind(that));
	},

	onListTasksCompleted: function(data){
		var container = this.createTaskMetricsTable(data);
		if(data._links !== undefined && data._links["next"] !== undefined) {
			var limit = this.getTasksPageSize() + this.getNextTasksPageSize();
			container.append(
				'<div class="text-center">' +
						'<a rel="next" href="'+data._links["next"]["href"]+'?limit=' + limit + '" data-action="nextPage">More</a>' +
				'</div>'
			);
		}
	},

	createTaskMetricsTable(data){
		var tasks = data._embedded['ora:task'];
		var container = $('#task-metrics');
		var that = this;
		var html = "";
		html += "<table class=\"table table-hover\">";
		html += "<thead>" +
					"<tr>" +
						"<th class=\"text-center\" style=\"width: 3em\"></th>" +
						"<th class=\"text-left\">Subject</th>" +
						"<th class=\"text-right\" style=\"width: 6em\">Credits</th>" +
						"<th class=\"text-right\" style=\"width: 8em\">&Delta; shares" +
					"</tr>" +
				"</thead>";
		html += "<tbody>";
		if(!$.isEmptyObject(tasks)){
			$.each(tasks, function(key, task) {
				if(key.toUpperCase() == 'STATS'){
					var countTasksOwner = task.countTasksOwner;
					var countTasksMember = data.total;
					var sumTasksCredits = task.sumTasksCredits;
					var averageOfDeltaShares = task.averageOfDeltaShares;
					html += "<tr style=\"border-top: 2px solid darkgray;\">" +
								"<td class=\"text-center\">"+countTasksOwner+"</td>" +
								"<td class=\"text-left\">"+countTasksMember+"</td>" +
								"<td class=\"text-right\">"+sumTasksCredits+"</td>" +
								"<td class=\"text-right\">AVG:&nbsp&nbsp"+(averageOfDeltaShares* 100).toFixed(2)+" %</td>";
				}else{
					html += "<tr data-id='"+task.id+"'>";
					var isOwner = false;
					var delta = null;
					var credits = null;
					$.each(task.members, function(memberId, info){
						if(memberId == that.getUserId()){
							isOwner = that.taskUtils.isTaskOwner(info.role);
							if(info.delta !== undefined){
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
					html += credits !== null ? "<td class=\"text-right\">"+credits+"</td>" : "<td></td>";
					html += delta !== null ? "<td class=\"text-right\">"+delta+"</td>" : "<td></td>";
					}
				html += "</tr>";
			});
		}else{
			html += "<tr><td colspan=\"4\">No Tasks metrics available</td></tr>";
		}
		html += "</tbody>" +
			"</table>";
		container.html(html);
		return container;
	},

	listMoreTasks: function(e){
		var url = $(e.target).attr('href');
		if(this.getEndOn()){
			url += "&endOn="+this.getEndOn();
		}
		if(this.getStartOn()){
			url += "&startOn="+this.getStartOn();
		}
		if(this.getUserId()){
			url += "&memberId="+this.getUserId();
		}
		var that = this;
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'GET',
		}).done(function(json){
			that.setTasksPageSize(json.count);
			that.onListTasksCompleted.bind(that, json)();
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
	profile.listTasks("/"+profile.getOrgId()+"/task-management/tasks?memberId="+profile.getUserId());
});