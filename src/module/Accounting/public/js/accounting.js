/**
 * 
 */
var Accounting = function()
{
	this.bindEventsOn(); 
};

Accounting.prototype = {
		constructor : Accounting,
		classe : 'Accounting',
		
		bindEventsOn: function()
		{
			var that = this;

			$().ready(function(e){
				e.preventDefault();
				that.listAccounts();
			});
		},

		listAccounts: function()
		{
			$.ajax({
				url: basePath + '/accounting/accounts',
				method: 'GET',
				dataType: 'json'
			})
			.done(this.onListAccountsCompleted.bind(this));
		},

		onListAccountsCompleted: function(json)
		{
			var container = $('.jumbotron').closest('.container');	// TODO: Da cambiare
	
			container.empty();
			if ($(json.accounts).length > 0)
			{
				account = json.accounts[0];
				container.append("<p>Balance at " + account.balance.date + ": " + account.balance.value + "</p>");
			}
	
			container.append('<ul>');
			$.each(json.accounts, function(key, account) {
				container.append('<li><a href="' + account._links.self + '"></a>Account ' + account.id + '</li>')
			});
			container.append('</ul>');
		},
}