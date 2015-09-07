var Organizations = function()
{
	this.bindEventsOn();
};

Organizations.prototype = {

	constructor: Organizations,
	classe: 'Organizations',
	
	data: [],
	
	membershipsData: null,

	bindEventsOn: function()
	{
		var that = this;

		$('div#sign-in').on('loggedIn', function(e, user) {
			$(this).hide();
			sessionStorage.setItem('token', user.token);
			console.log("ID Token saved in client session");
			sessionStorage.setItem('avatar', user.avatar);
			sessionStorage.setItem('email', user.email);
			that.init();
			that.loadMyOrganizations();
		});

		$('div#sign-in').on('loggedOut', function(e) {
			sessionStorage.clear();
			console.log("Client session cleared");
			window.location = '/';
		});

		$("body").on("click", "a[data-action='loadOrganization']", function(e){
			window.location = + e.target.href;
		});

		$("#createOrganizationModal").on("show.bs.modal", function(e) {
			var modal = $(this);
			modal.find('div.alert').hide();
			modal.find("form")[0].reset();
		});

		$("#createOrganizationModal").on("submit", "form", function(e){
			e.preventDefault();
			that.createOrganization(e);
		});

		$("body").on("click", "a[data-action='joinOrganization']", function(e){
			e.preventDefault();
			that.joinOrganization(e);
		});

		$("body").on("click", "a[data-action='unjoinOrganization']", function(e){
			e.preventDefault();
			that.unjoinOrganization(e);
		});
	},

	loadMyOrganizations: function () {
		$.ajax({
			url: '/memberships',
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			}
		}).done(this.showMyOrganizations.bind(this));
	},

	showMyOrganizations: function(json)
	{
		var container = $('#organizations ul').first().empty();
		$.each(json._embedded['ora:organization-membership'], function(i, object) {
			container.append('<li><a href="' + object.organization._links['ora:task'].href + '" data-action="loadOrganization">' + object.organization.name + '</li>');
		});
		$('#organizations').show();
	},
	
	createOrganization: function(e)
	{
		var form = $(e.target);
		var url = form.attr('action');
		var modal = $(e.delegateTarget);
		var that = this;
		
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'POST',
			data: form.serialize(),
			success: function() {
				modal.modal('hide');
				that.loadMyOrganizations();
			},
			error: function(jqHXR, textStatus, errorThrown) {
				json = $.parseJSON(jqHXR.responseText);
				if(json.description != undefined) {
					that.show(m, 'danger', json.description);
				}
				if(json.errors != undefined) {
					that.show(m, 'danger', json.errors[0].message);
				}
			}
		});
	},

	joinOrganization: function(e)
	{
		var url = $(e.target).attr('href');
		var that = this;

		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'POST',
			complete: function(xhr, textStatus) {
				m = $('#content');
				if (xhr.status === 201) {
					that.show(m, 'success', 'You successfully joined the organization');
					that.loadOrganizations();
				}
				else if (xhr.status === 204) {
					that.show(m, 'warning', 'You are already member of the organization');
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to join the organization');
				}
			}
		});

	},

	unjoinOrganization: function(e)
	{
		var url = $(e.target).attr('href');
		var that = this;

		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'DELETE',
			complete: function(xhr, textStatus) {
				m = $('#content');
				if (xhr.status === 200) {
					that.show(m, 'success', 'You successfully unjoined the organization');
					that.loadOrganizations();
				}
				else if (xhr.status === 204) {
					that.show(m, 'warning', 'You are already not a member of the organization');
				}
				else {
					that.show(m, 'danger', 'An unknown error "' + xhr.status + '" occurred while trying to unjoin the organization');
				}
			}
		});
	},

	loadOrganizations: function () {
		var that = this;

		$.ajax({
			url: 'people/organizations',
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'GET',
		}).done(that.onLoadOrganizationsCompleted.bind(this));
	},

	onLoadOrganizationsCompleted: function(json)
	{
		var container = $('#organizations');
		container.empty();

		if (json.count.length == 0) {
			container.append("<p>No organizations found</p>");
		} else {
			var that = this;
			$.each(json._embedded['ora:organization'], function(key, org) {
				if(org.membership) {
					member = '<a href="' + org._links['ora:member'].href + '" class="btn btn-warning" data-action="unjoinOrganization">Unjoin</a>';
				} else {
					member = '<a href="' + org._links['ora:member'].href + '" class="btn btn-info" data-action="joinOrganization">Join</a>';
				}
				container.append('<li style="margin-bottom: 5px"><span>' + org.name + '</span> ' + member + '</li>');
			});
		}
	},

	init: function()
	{
		if(sessionStorage.getItem('token')) {
			$('#identityMenu li:first a')
				.text(sessionStorage.getItem('email'))
				.prepend('<img src="' + sessionStorage.getItem('avatar') + '" alt="Avatar" style="max-width: 20px; max-height: 20px;" class="img-circle">');
			$('#identityMenu').show();
		}
	},

	show: function(container, level, message) {
		var alertDiv = container.find('div.alert');
		alertDiv.removeClass();
		alertDiv.addClass('alert alert-' + level);
		alertDiv.text(message);
		alertDiv.show();
	}
};
$().ready(function(e){
	organizations = new Organizations();
	organizations.init();
});