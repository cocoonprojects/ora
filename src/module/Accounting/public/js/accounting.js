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

			$("a.statement").on("click", function(e){
				e.preventDefault();
				that.listTransactions(this.href);
			});
			
		},

		listAccounts: function()
		{
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

		onListAccountsCompleted: function(json)
		{
			var container = $('#accounts');
			container.empty();
			
			$.each(json.accounts, function(key, account) {
				balanceDate = new Date(Date.parse(account.balance.date));
				s = '<li>Account ' + account.id + ' balance at ' + balanceDate.toLocaleString() + ': ' + account.balance.value;
				if(account._links.statement != undefined) {
					s += ' <a href="' + account._links.statement + '" class="statement">Transactions</a>';
				}
				container.append(s + '</li>');
				if(account._links.deposits != undefined) {
					container.append('<form method="POST" action="' + account._links.deposits + '"><input type="text" name="amount"/><button type="submit">Deposit</button></form>');
				}
			});
		},
		
		onListTransactionsCompleted: function(json)
		{
			var container = $('#transactions');
			container.empty();
			var top = $('#actual-balance');
			top.empty();
			var bottom = $('#starting-balance');
			
			$.each(json.statement.transactions, function(key, transaction) {
				transactionDate = new Date(Date.parse(transaction.date));
				if(key == 0) { top.append(transaction.balance); }
				cssClass = transaction.type == 'Deposit' ? 'text-success' : '';
				container.append('<tr><td>' + transactionDate.toLocaleString() + '</td><td>' + transaction.description + '</td><td>' + transaction.type + '</td><td class=' + cssClass + '>' + transaction.amount + '</td></tr>');
				bottom.empty();
				bottom.append(transaction.balance);
			});
			
		}
}

$().ready(function(e){
	accounting = new Accounting();
	accounting.listAccounts();
});