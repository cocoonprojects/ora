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

			$("body").on("click", "#accounts li[role='presentation'] a", function(e){
				e.preventDefault();
				$("#accounts li[role='presentation']").removeClass('active');
				li = $(this).parent();
				li.attr('class', 'active');
				that.listTransactions(this.href);
			});
			
			$("#depositModal").on("show.bs.modal", function(e) {
				var button = $(e.relatedTarget) // Button that triggered the modal
				var url = button.data('href');
				form = $(this).find("form");
				form.attr("action", url);
				form[0].reset();
				$(this).find('div.alert').hide();
			});

			$("#depositModal").on("submit", "form", function(e){
				e.preventDefault();
				that.depositCredits(this);
			});
			
		},

		listAccounts: function()
		{
			$('#account').hide();

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
					switch(xhr.status) {
					case 201:
						href = $('li.active[role="presentation"] a').attr('href');
						that.listTransactions(href);
						$('#depositModal').modal('hide');
						break;
					case 400:
						that.show('warning', 'One of the specified values isn\'t valid');
						break;
						default:
							that.show('danger', 'An unknown error "' + xhr.status + '" occurred while trying to edit the task');
					}
				}
			});
		},

		onListAccountsCompleted: function(json)
		{
			var container = $('#accounts');
			container.empty();
						
			$.each(json.accounts, function(key, account) {
				s = account.organization == undefined ? 'My account' : account.organization + ' account';
				container.append('<li role="presentation"><a href="' + account._links.statement + '">' + s + '</a></li>');
			});
		},
		
		onListTransactionsCompleted: function(json)
		{
			var container = $('#account');

//			if(json.organization != undefined) {
//				container.find('h2').text(json.organization + ' account');
//			} else {
//				container.find('h2').text('My account');
//			}
			
			balanceDate = new Date(Date.parse(json.balance.date));
			p = container.find('p');
			p.html('<span class="text-primary">' + json.balance.value + ' credits</span> at ' + balanceDate.toLocaleString());
			p.append('<ul role="menu">');
			if(json._links.deposits != undefined) {
				p.append('<li><a href="#" data-href="' + json._links.deposits + '" class="btn btn-default" data-toggle="modal" data-target="#depositModal">Deposit</a></li>');
			}
			p.append('</ul>');

			c = container.find('tbody').empty();
			var top = $('#actual-balance');
			top.text(0);
			var bottom = $('#starting-balance');
			bottom.text(0);
			
			$.each(json.transactions, function(key, transaction) {
				transactionDate = new Date(Date.parse(transaction.date));
				if(key == 0) { top.text(transaction.balance); }
				cssClass = transaction.type == 'Deposit' ? 'text-success' : '';
				c.append('<tr><td>' + transactionDate.toLocaleString() + '</td><td>' + transaction.description + '</td><td>' + transaction.type + '</td><td class=' + cssClass + '>' + transaction.amount + '</td></tr>');
				bottom.empty();
				bottom.text(transaction.balance);
			});
			if(json.transactions.length == 0) {
				c.append('<tr><td colspan="4">No transactions in your history</td></tr>');
			}
			
			container.show();
		},
		
		show: function(level, message) {
			alertDiv = $('#depositModal div.alert');
			alertDiv.removeClass();
			alertDiv.addClass('alert alert-' + level);
			alertDiv.text(message);
			alertDiv.show();
		},
}

$().ready(function(e){
	$('#firstLevelMenu li').eq(1).addClass('active');
	accounting = new Accounting();
	accounting.listAccounts();
});