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
		
		$('#userMenu').on('show.bs.dropdown', function(e) {
			container = $(e.target);
			$('li.membership').remove();
			if(that.membershipsData == null) {
				$('<li class="membership"><a href="#">Loading...</a></li>').insertBefore('#myOrgDevider');
			} else {
				$.each(that.membershipsData._embedded['ora:organization-membership'], function(i, object) {
					$('<li class="membership"><a href="#" data-url="people/organizations/' + object.organization.id + '/members" data-action="loadPeople">' + object.organization.name + '</a></li>').insertBefore('#myOrgDevider');
				});
			}
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
	},

	createOrganization: function(e)
	{
		var form = $(e.target);
		var url = form.attr('action');
		var modal = $(e.delegateTarget);
	
		var that = this;
		
		$.ajax({
			url: url,
			method: 'POST',
			data: form.serialize(),
			success: function() {
				modal.modal('hide');
				that.updateMemberships();
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

	onLoadOrganizationsCompleted: function()
	{
		var container = $('#organizations');
		container.empty();

		var organizations = this.data._embedded['ora:organization'];

		if ($(organizations).length == 0) {
			container.append("<p>No organizations found</p>");
		} else {
			var that = this;
			$.each(organizations, function(key, org) {
				member = '<button type="button" class="btn btn-info">Join</button>';
				$.each(that.membershipsData._embedded['ora:organization-membership'], function(i, object) {
					if(object.organization.id == org.id) {
						member = '<button type="button" class="btn btn-warning">Unjoin</button>';
					}
				});

				container.append('<li style="margin-bottom: 5px"><span>' + org.name + '</span> ' + member + '</li>');
			});
		}
	},

	init: function()
	{
		var that = this;
		if($("ol#organizations").length) {
			$.when($.ajax('people/organizations'), $.ajax('/memberships')).done(function(orgs, myorgs) {
				that.setMembershipsData(myorgs[0]);
				that.setOrganizationsData(orgs[0]);
				that.onLoadOrganizationsCompleted();
			});
		} else {
			$.getJSON('/memberships', function(data) { that.setMembershipsData(data); } );
		}
	},

	setMembershipsData: function(data)
	{
		this.membershipsData = data;
	},

	setOrganizationsData: function(data)
	{
		this.data = data;
	},
	
	show: function(container, level, message) {
		alertDiv = container.find('div.alert');
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