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
	
	bindEventsOn: function()
	{
		var that = this;
	        
		// CREATE NEW TASK
		$("body").on("submit", "#formCreateNewTask", function(e){
			e.preventDefault();
			that.createNewTask();
		});
		
		// OPEN EDIT TASK BOX
		$("body").on("click", "button[data-action='openEditTaskBox']", function(e){
			e.preventDefault();
			that.openEditTaskBox(e);
		});
		
		// EDIT TASK
		$("body").on("submit", "#formEditTask", function(e){
			e.preventDefault();
			that.editTask(e);
		});
		
		// DELETE TASK
		$("body").on("click", "button[data-action='deleteTask']", function(e){
			e.preventDefault();
			that.deleteTask(e);
		});
		
		// JOIN TASK MEMBERS
		$("body").on("click", "button[data-action='joinTask']", function(e){
			e.preventDefault();
			that.joinTaskMembers(e);
		});
		
		// UNJOIN TASK MEMBERS
		$("body").on("click", "button[data-action='unjoinTask']", function(e){
			e.preventDefault();
			that.unjoinTaskMembers(e);
		});

        //ACCEPT TASK FOR KAMBANIZE       
		$("body").on("click", "button[data-action='acceptTask']", function(e){
			e.preventDefault();
			that.acceptTask(e);
		});

        //BACK TO ONGOING             
		$("body").on("click", "button[data-action='back2ongoingTask']", function(e){
			e.preventDefault();
			that.backToOngoingTask(e);
		});
		//INSERT ESTIMATION
		$("body").on("click", "button[data-action='makestima']", function(e){
			//alert ("stima" );
			that.showDialog(e);
		//	that.makeEstimation(e);
			
		});
	},
	
	unjoinTaskMembers: function(e)
	{
		var taskID = $(e.target).closest("tr").data("taskid");
		var userID = $(e.target).closest("tr").data("userid");
		
		$.ajax({
			url: '/task-management/tasks/' + taskID + '/members',
			method: 'DELETE',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 200)
					alert("Succesfully unjoined from the Team of this task");
				else if (xhr.status === 403)
					alert("Error. You are not part of that team or the creator of this task");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
	joinTaskMembers: function(e)
	{
		var taskID = $(e.target).closest("tr").data("taskid");
		var userID = $(e.target).closest("tr").data("userid");
		
		$.ajax({
			url: '/task-management/tasks/' + taskID + '/members',
			method: 'POST',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 201)
					alert("Succesfully join into the Team of this task");
				else if (xhr.status === 403)
					alert("Error. You are already part of that team or the creator of this task");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
	openEditTaskBox: function(e)
	{
		var taskID = $(e.target).closest("tr").data("taskid");
		var taskSubject = $(e.target).closest("tr").data("tasksubject");

		$("#editTaskBox").show();
		$("#inputEditTaskID").val(taskID);
		$('#inputEditTaskSubject').val(taskSubject);
		
	},
	
	editTask: function(e)
	{
		var taskID = $("#inputEditTaskID").val();

		$.ajax({
			url: '/task-management/tasks/' + taskID,
			method: 'PUT',
			data: $('#formEditTask').serialize(),
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 202)
					alert("Task edited succesfully");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
	deleteTask: function(e)
	{
		var taskID = $(e.target).closest("tr").data("taskid");
		
		if (confirm('Are you sure you want to delete this entity?'))
			this.deleteTaskConfirmed(taskID);
		else
			alert("Operation aborted...");
	},
	
	deleteTaskConfirmed: function(taskID)
	{
		$.ajax({
			url: '/task-management/tasks/' + taskID,
			method: 'DELETE',
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 200)
					alert("Task deleted succesfully");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},

	acceptTask: function(e){
	        //$this->basePath("/kanbanize/task/".$singletask->getId()."/client/test?method=accept"); ?>">
	                var taskID = $(e.target).closest("tr").data("taskid");

	                // make a post to restful controller
	                $.ajax({
	                        url: '/task-management/tasks/'+taskID+'/transitions',
	                        method: 'POST',
	                        data:{action:'accept'},
	                        dataType: 'json',
	                        complete: function(xhr, textStatus) {
	                                if (xhr.status === 200)
	                                        alert("Succesfully accepted task in kanbanize");
	                                else if (xhr.status === 400)
	                                        alert("Error. Cannot accept task");
	                                else if (xhr.status === 204)
	                                	 alert("Error. Already in accepted status");
	                                else
	                                        alert("Error. Status Code: " + xhr.status);
	                        }
	                });
	    },


    
    backToOngoingTask: function(e){
    	 //$this->basePath("/kanbanize/task/".$singletask->getId()."/client/test?method=accept"); ?>">
        var taskID = $(e.target).closest("tr").data("taskid");

        // make a post to restful controller
        $.ajax({
                url: '/task-management/tasks/'+taskID+'/transitions',
                method: 'POST',
                data:{action:'ongoing'},
                dataType: 'json',
                complete: function(xhr, textStatus) {
                        if (xhr.status === 200)
                                alert("Succesfully moved ongoing  task in kanbanize");
                        else if (xhr.status === 400)
                                alert("Error. Cannot move task");
                        else if (xhr.status === 204)
                        	 alert("Error. Already in ongoing status");
                        else
                                alert("Error. Status Code: " + xhr.status);
                }
        });
},

    listAvailableTask: function()
	{
		$.ajax({
			url: '/task-management/tasks',
			method: 'GET',
			dataType: 'json'
		})
		.done(this.onListAvailableTaskCompleted.bind(this));
	},
	
	onListAvailableTaskCompleted: function(json)
	{
		var container = $('#tasks');
		container.empty();
		
		if ($(json.tasks).length == 0) {
			container.append("<tr><td colspan='6'>No available tasks found</td></tr>");
		} else {
			that = this;
			$.each(json.tasks, function(key, task) {
				subject = task._links.self == undefined ? task.subject : '<a href="' + task._links.self + '">' + task.subject + '</a>';
				createdAt = new Date(Date.parse(task.createdAt));
				estimation = task.estimation;
				var actions = "";
				if (task._links.join != undefined) {
					actions += '<button data-action="unjoinTask" class="btn btn-default">Join</button>';
				}
				if (task._links.unjoin != undefined) {
					actions += '<button data-action="joinTask" class="btn btn-default">Unjoin</button>';
				}
				if (task._links.edit != undefined) {
					actions += '<button data-action="openEditTaskBox" class="btn btn-default">Edit</button>';
				}
				if (task._links['delete'] != undefined) {
					actions += '<button data-action="deleteTask" class="btn btn-default">Delete</button>';
				}
				if (task._links.estimate != undefined) {
					actions += '<button data-action="makestima" class="btn btn-default">Estimate</button>';
				}
				if (task._links.execute != undefined) {
					actions += '<button data-action="back2ongoingTask" class="btn btn-default">Ongoing</button>';
				}
				if (task._links.complete != undefined) {
					actions += '<button class="btn btn-default">Complete</button>';
				}
				if (task._links.accept != undefined) {
					actions += '<button data-action="acceptTask" class="btn btn-default">Accept</button>';
				}
				if (task._links.assignShares != undefined) {
					actions += '<button class="btn btn-default">Assign share</button>';
				}
				switch(task.estimation) {
				case -1:
					estimation = 'Skipped';
					break;
				case null:
					estimation = 'In progress';
					break;
				}

				container.append(
						/*"<tr data-taskid='"+task.id+"' data-tasksubject='" + task.subject + "' data-userid='"+json.loggeduser.id+"'>" +	*/
                        "<tr>" +
							'<td>' + subject + '</td>' +
							"<td>" + createdAt.toLocaleString() + "</td>" +
                            "<td>" + $.map(task.members, function(object,key) {
                            	rv = '<span class="task-member">' + object.firstname + " " + object.lastname;
                                if(object.estimation != null){
                                    rv += '<img src="/img/tick10.png">';
                                }
                                return rv + '</span>';
                            }).join('') + "</td>" + 
							'<td>' + that.statuses[task.status] + '</td>' +
							'<td>' + estimation + '</td>' +
							'<td>' + actions + '</td>' +
						"</tr>");
			});
		}
	},
	
	createNewTask: function()
	{
		$.ajax({
			url: '/task-management/tasks',
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
	
	makeEstimation : function(id , value){
		 var taskID = $(e.target).closest("tr").data("taskid");
		 alert (taskID);
		$.ajax({
			url: '/task-management/tasks/'+taskID+'/estimation',
			method: 'POST',
			data: {value:100},
			dataType: 'json',
			complete: function(xhr, textStatus) {
				if (xhr.status === 201)
					alert("Estimation done");
				else
					alert("Error. Status Code: " + xhr.status);
			}
		});
	},
	
	showDialog : function (e){
		 var taskID = $(e.target).closest("tr").data("taskid");
		// alert("show dialog");
		 $("#modalestimation").remove();
		 var modaltoappend = "<div class='modal fade' id='modalestimation'>"+
		 						"<div class='modal-dialog'>"+
		 							"<div class='modal-content'>"+
		 								"<div class='modal-header'>"+
		 									"<button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>"+
		 									"<h4 class='modal-title'>Inserisci Stima</h4>"+
		 								"</div>"+
		 								"<div class='modal-body'>"+
		 								"<div class='form-group' id='formmodal'>"+
		 								"<div class='checkbox'>"+
		 							    "<label>"+
		 							      "<input type='checkbox' id ='checkstima' > Non voglio stimare"+
		 							    "</label>"+"<br> <br>"+
		 							   "<label for='valuestima' id ='labelstima'>Valore Stima</label>"+
		 							   "<input type='number' id='valuestima' pattern='/^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/' class='form-control' placeholder='Valore Stima'>"+
		 							   "</div>"+
		 							  "</div>"+
		 								"</div>"+
		 								"<div class='modal-footer'>"+
		 									"<button type='button' class='btn btn-default' id ='closemodal'>Close</button>"+
		 									"<button type='button' class='btn btn-primary' id='confirmestimation'>Confirm</button>"+
		 								"</div>"+
		 							"</div><!-- /.modal-content -->"+
		 						"</div><!-- /.modal-dialog -->"+
		 					"</div><!-- /.modal -->";
		 $("body").append(modaltoappend);
		 $("#modalestimation").modal({keyboard: true});
		 $('#checkstima').click(function() {
			    var $this = $(this);
			    // $this will contain a reference to the checkbox   
			    if ($this.is(':checked')) {
			    	$("#valuestima").prop('disabled', true);
			    } else {
			        // the checkbox was unchecked
			    	$("#valuestima").prop('disabled', false);
			    }
			});
		 $("#closemodal").click(function(){
			 $("#modalestimation").modal('hide');
			
		 });
		 $("#confirmestimation").click(function(){
			 var valuetosubmit;
		
				if ($('#checkstima').is(':checked') ){
					valuetosubmit = -1;
				}else{
					 var valuetosubmit = $("#valuestima").val();
					 if (!($.isNumeric(valuetosubmit)&&valuetosubmit>0)){
						 alert ("valore non conforme");
						 $("#formmodal").addClass("has-error");
						 return;
					 }
						 
				}
				
				//alert(taskID+" ----------> "+valuetosubmit);
				$.ajax({
					url: '/task-management/tasks/'+taskID+'/estimation',
					method: 'POST',
					data: {value:valuetosubmit},
					dataType: 'json',
					complete: function(xhr, textStatus) {
						if (xhr.status === 201)
							alert("Estimation done");
						else
							alert("Error. Status Code: " + xhr.status);
						 $("#modalestimation").modal('hide');
					}
				});
				
				
				

		 });
		 
	}
	
};

$().ready(function(e){
	collaboration = new TaskManagement();
	collaboration.listAvailableTask();
});