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

		$("body").on("click", "a[data-action='loadPeople']", function(e){
			e.preventDefault();
			var anchor = $(e.target);
			that.loadPeople(anchor.data('url'));
		});
	},

	loadPeople: function(url)
	{
		that = this;
		$.getJSON(url, function(data) {
			that.data = data;
			that.onLoadPeopleCompleted();
		});
	},

	onLoadPeopleCompleted: function()
	{
		var container = $('#people');
		container.empty();

		var members = this.data._embedded['ora:organization-member'];

		if ($(members).length == 0) {
			container.append("<p>No members found</p>");
		} else {
			var that = this;
			$.each(members, function(key, member) {
				container.append('<li style="margin-bottom: 5px"><img src="' + member.picture + '" style="max-width: 60px; max-height: 60px;" class="img-circle"> <a href="#" data-url="people/users/' + member.id + '"><span class="firstname">' + member.firstname + '</span> <span class="lastname">' + member.lastname + '</span></a></li>')
			});
		}
	}
};

$().ready(function(e){
	people = new People(organizations);
});