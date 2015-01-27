var taskManagementServices = angular.module('taskManagementServices', ['ngResource']);

taskManagementServices.factory('Task', ['$resource',
    function($resource){
		return $resource('task-management/:taskId.json', {}, {
			query: {method:'GET', params:{taskId:'tasks'}, isArray:true}
		});
	}]);