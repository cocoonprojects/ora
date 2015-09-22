var People = function(){
};

People.prototype = {

	constructor: People,
	classe : 'People',
	data : [],

	loadPeople: function(url)
	{
		that = this;
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},

		}).done(that.onLoadPeopleCompleted.bind(this));
	},

	onLoadPeopleCompleted: function(json)
	{
		this.data = json;
		var container = $('#people');
		container.empty();

		var members = this.data._embedded['ora:organization-member'];

		if ($(members).length == 0) {
			container.append("<p>No members found</p>");
		} else {
			var that = this;
			$.each(members, function(key, member) {
				container.append('<li style="margin-bottom: 5px"><img src="' + member.picture + '" style="max-width: 60px; max-height: 60px;" class="img-circle"> <a href="profiles/'+member.id+'" data-action="user-detail" data-user=' + member.id + '"><span class="firstname">' + member.firstname + '</span> <span class="lastname">' + member.lastname + '</span></a></li>')
			});
		}
	}
};

$().ready(function(e){
	people = new People();
	people.loadPeople($("#people-home").attr('href')+'/members');
});