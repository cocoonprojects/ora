var taskManagementControllers = angular.module('taskManagementControllers', []);

taskManagementControllers.controller('TaskListCtrl', ['$scope', 'Task',
    function ($scope, Task) {
		$scope.tasks = Task.query();
	}
]);

taskManagementControllers.controller('TaskDetailCtrl', ['$scope', '$routeParams', 'Task',
    function($scope, $routeParams, Task) {
		$scope.task = Task.get({taskId : $routeParams.taskId }, function(task) {
			
		});
	}
]);