var Profile = function() {

	this.getCurrentDate = function(){
		var currentDate = new Date();
		return currentDate.toJSON().slice(0, 10);
	};

	var tasksPageSize = 10,
		nextTasksPageSize = 10,
		endOn = this.getCurrentDate(),
		startOn = "",
		userId = "",
		orgId = "";

	this.TASK_ROLE_OWNER = 'OWNER';
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

			var inputFrom = $("#inputFrom").val() !== "" ? $("#inputFrom").val().split("/", 3) : "";
			var inputTo = $("#inputTo").val() !== "" ? $("#inputTo").val().split("/", 3) : "";

			if(inputFrom.length == 3){
				that.setStartOn(inputFrom[2]+"-"+inputFrom[1]+"-"+inputFrom[0]);
			}else{
				that.setStartOn("");
			}
			if(inputTo.length == 3){
				that.setEndOn(inputTo[2]+"-"+inputTo[1]+"-"+inputTo[0]);
			}else{
				that.setEndOn(that.getCurrentDate());
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
		var tableObject = this.createTaskMetricsObject(data._embedded['ora:task']);
		var container = this.createTaskMetricsTable(tableObject);
		if(data._links !== undefined && data._links["next"] !== undefined) {
			var limit = this.getTasksPageSize() + this.getNextTasksPageSize();
			container.append(
				'<div class="text-center">' +
						'<a rel="next" href="'+data._links["next"]["href"]+'?limit=' + limit + '" data-action="nextPage">More</a>' +
				'</div>'
			);
		}
	},

	createTaskMetricsObject(tasks){

		var that = this;
		var tableObject = {};
		tableObject.rows = [];
		var row_total = {
				id : 'total',
				countOwners : 0,
				countTasks : 0,
				sumCredits : 0,
				sumDeltas : 0,
				deltaAverage : 0,
				countTasksWithShares: 0
		};
		$.each(tasks, function(key, task) {
			row_total.countTasks++;
			var row = {
				id : task.id,
				credits : 0,
				subject : task.subject,
				delta : null,
				owner : false,
				link : task._links.self
			};
			for(var memberId in task.members) {
				var info = task.members[memberId];
				if(memberId == that.getUserId()){
					if(info.role.toUpperCase() == that.TASK_ROLE_OWNER.toUpperCase()) {
						row.owner = true;
						row_total.countOwners++;
					}
					if(info.delta !== undefined){
						row.delta = parseFloat(info.delta);
						row_total.sumDeltas += row.delta;
						row.delta = (row.delta * 100).toFixed(2) + '%';
						row_total.countTasksWithShares++;
					}
					row.credits = info.credits !== undefined ? info.credits : 0;
					row_total.sumCredits += row.credits;
				}
			}

			tableObject.rows.push(row);
		});
		if(row_total.sumDeltas !== 0){
			row_total.deltaAverage = (row_total.sumDeltas / row_total.countTasksWithShares * 100).toFixed(2) + '%';
		}
		tableObject.rows.push(row_total);
		return tableObject;
	},

	createTaskMetricsTable(tableObject){
		var container = $('#task-metrics');
		var html = "";
		html += "<table class=\"table table-hover text-center\">";

		if(Object.keys(tableObject.rows).length > 1){

			html += "<thead>" +
						"<tr>" +
							"<th class=\"text-center col-md-1\"></th>" +
							"<th class=\"text-center col-md-7\">Subject</th>" +
							"<th class=\"text-center col-md-2\">Credits</th>" +
							"<th class=\"text-center col-md-2\">&Delta; shares" +
						"</tr>" +
					"</thead>";
			html += "<tbody>";
			for(var key in tableObject.rows){
				var row = tableObject.rows[key];
				if(row.id == 'total'){
					html += "<tr style=\"border-top: 2px solid darkgray;\" data-id='"+row.id+"'>" +
								"<td>"+row.countOwners+"</td>" +
								"<td>"+row.countTasks+"</td>" +
								"<td>"+row.sumCredits+"</td>" +
								"<td>AVERAGE: "+row.deltaAverage+"</td>";
				}else{
					html += "<tr data-id='"+row.id+"'>";
					if(row.owner){
						html += "<td><i class=\"mdi-action-grade\" title=\"owner\"></i></td>";
					}else{
						html += "<td></td>";
					}

					html += "<td>"+row.subject+"</td>" +
							"<td>"+row.credits+"</td>";
					html += row.delta !== null ? "<td>"+row.delta+"</td>" : "<td></td>";
				}
				html += "</tr>";
			}
			html += "</tbody>";
		}else{
			html += "<tr><td>No Tasks metrics available</td></tr>";
			html += "</tbody></table>";
		}

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
	profile = new Profile();
	$('#profile-content').hide();
	var elem = document.getElementById("profile-content");
	profile.setOrgId(elem.getAttribute("org-id"));
	profile.setUserId(elem.getAttribute("user-id"));
	var url = "/"+profile.getOrgId()+"/user-profiles/"+profile.getUserId();
	var redirectURL = "/"+profile.getOrgId()+"/profiles/"+profile.getUserId();
	profile.loadUserDetail(url, redirectURL);
	profile.listTasks("/"+profile.getOrgId()+"/task-management/tasks?endOn="+profile.getCurrentDate()+"&memberId="+profile.getUserId());
});