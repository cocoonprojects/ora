var People = function(oranizations)
{
	this.bindEventsOn();
	
	this.organizations = organizations;
};

People.prototype = {

	constructor: People,
	classe : 'People',
	data : [],
	
	bindEventsOn: function()
	{
		var that = this;
	},
};

$().ready(function(e){
	people = new People(organizations);
});