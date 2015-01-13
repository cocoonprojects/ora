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

			$("body").on("click", "a.statement", function(e){
				e.preventDefault();
				that.listTransactions(this.href);
			});
			
			$("#depositModal").on("show.bs.modal", function(e) {
				var button = $(e.relatedTarget) // Button that triggered the modal
				var url = button.data('href');
				$(this).find("form").attr("action", url);
			});

			$("#depositModal").on("submit", "form", function(e){
				e.preventDefault();
				that.depositCredits(this);
			});
			
		},

		listAccounts: function()
		{
			$('#transactions-container').hide();

			$.ajax({
				url: '/accounting/accounts',
				method: 'GET',
				dataType: 'json'
			})
			.done(this.onListAccountsCompleted.bind(this));
		},
		
		listTransactions: function(url)
		{
			$.ajax({
				url: url,
				method: 'GET',
				dataType: 'json'
			})
			.done(this.onListTransactionsCompleted.bind(this));
		},
		
		depositCredits: function(form)
		{
			jform = $(form);
			that = this;
			$.ajax({
				url: jform.attr('action'),
				method: 'POST',
				data: jform.serialize(),
				dataType: 'json',
				complete: function(xhr, textStatus) {
					if (xhr.status === 201) {
						that.listAccounts();
						$('#depositModal').modal('hide');
					}
					else {
						alertDiv = $('#depositModal div.alert');
						alertDiv.removeClass();
						alertDiv.addClass('alert alert-danger');
						alertDiv.text('An unknown error "' + xhr.status + '" occurred while trying to edit the task');
					}
				}
			});
		},

		onListAccountsCompleted: function(json)
		{
			var container = $('#accounts');
			container.empty();
						
			$.each(json.accounts, function(key, account) {
				balanceDate = new Date(Date.parse(account.balance.date));
				s= account.organization == undefined ? 'My account' : account.organization + ' account';
				s = '<li><h4>' + s + ' balance: <span class="text-primary">' + account.balance.value + ' credits</span> at ' + balanceDate.toLocaleString() + '</h4>';
				if(account._links.statement != undefined) {
					s += ' <a href="' + account._links.statement + '" class="btn btn-default statement">View transactions</a>';
				}
				if(account._links.deposits != undefined) {
					s += ' <a href="#" data-href="' + account._links.deposits + '" class="btn btn-primary" data-toggle="modal" data-target="#depositModal">Deposit</a>';
				}
				container.append(s + '</li>');
			});
		},
		
		onListTransactionsCompleted: function(json)
		{
			var container = $('#transactions');
			container.empty();
			var top = $('#actual-balance');
			top.empty();
			var bottom = $('#starting-balance');
			
			var c = $('#transactions-container');
			if(json.organization != undefined) {
				c.find('h3').text(json.organization + ' transactions');
			} else {
				c.find('h3').text('My transactions');
			}
			
			$.each(json.transactions, function(key, transaction) {
				transactionDate = new Date(Date.parse(transaction.date));
				if(key == 0) { top.append(transaction.balance); }
				cssClass = transaction.type == 'Deposit' ? 'text-success' : '';
				container.append('<tr><td>' + transactionDate.toLocaleString() + '</td><td>' + transaction.description + '</td><td>' + transaction.type + '</td><td class=' + cssClass + '>' + transaction.amount + '</td></tr>');
				bottom.empty();
				bottom.append(transaction.balance);
			});
			
			c.show();
		}
}

$().ready(function(e){
	accounting = new Accounting();
	accounting.listAccounts();
});