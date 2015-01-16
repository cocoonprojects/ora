var taskManagementControllers = angular.module('taskManagementControllers', []);

taskManagementControllers.controller('TaskListCtrl', ['$scope', '$http',
    function ($scope, $http) {
		$http.get('task-management/tasks').success(function(data) {
			$scope.tasks = data.tasks;
		});
	}
]);

taskManagementControllers.controller('TaskDetailCtrl', ['$scope', '$routeParams', '$http',
    function($scope, $routeParams, $http) {
		$http.get('task-management/tasks/' + $routeParams.taskId).success(function(data) {
			$scope.task = data;
		});
	}
]);