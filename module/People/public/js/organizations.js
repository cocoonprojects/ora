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
				$('<li class="membership"><a href="#">Loading...</a></li>').insertBefore('#userMenu li.divider');
			} else {
				$.each(that.membershipsData._embedded['ora:organization-membership'], function(i, object) {
					$('<li class="membership"><a href="#" data-url="people/organizations/' + object.organization.id + '/members" data-action="loadPeople">' + object.organization.name + '</a></li>').insertBefore('#userMenu li.divider');
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
	
	updateMemberships: function()
	{
		var that = this;
		$.getJSON('/memberships', function(data) {
			that.membershipsData = data;
		});
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
	organizations.updateMemberships();
});