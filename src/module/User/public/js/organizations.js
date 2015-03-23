var Organizations = function()
{
	this.bindEventsOn();
};

Organizations.prototype = {

	constructor: Organizations,
	classe: 'Organizations',
	
	data: [],
	
	bindEventsOn: function()
	{
		var that = this;
        
		$("#createOrganizationModal").on("show.bs.modal", function(e) {
			modal = $(this);
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
	
	show: function(container, level, message) {
		alertDiv = container.find('div.alert');
		alertDiv.removeClass();
		alertDiv.addClass('alert alert-' + level);
		alertDiv.text(message);
		alertDiv.show();
	}
}

$().ready(function(e){
	organizations = new Organizations();
});