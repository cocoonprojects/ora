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
			var li = $(this).parent();
			li.attr('class', 'active');
			that.listTransactions(this.href);
		});

		$("#depositModal").on("show.bs.modal", function(e) {
			var button = $(e.relatedTarget) // Button that triggered the modal
			var url = button.data('href');
			var form = $(this).find("form");
			form.attr("action", url);
			form[0].reset();
			$(this).find('div.alert').hide();
		});

		$("#depositModal").on("submit", "form", function(e){
			e.preventDefault();
			that.depositCredits(e);
		});

		$("#withdrawalModal").on("show.bs.modal", function(e) {
			var button = $(e.relatedTarget) // Button that triggered the modal
			var url = button.data('href');
			var form = $(this).find("form");
			form.attr("action", url);
			form[0].reset();
			$(this).find('div.alert').hide();
		});

		$("#withdrawalModal").on("submit", "form", function(e){
			e.preventDefault();
			that.withdrawCredits(e);
		});

		$("#incomingTransferModal").on("show.bs.modal", function(e) {
			var button = $(e.relatedTarget) // Button that triggered the modal
			var url = button.data('href');
			var form = $(this).find("form");
			form.attr("action", url);
			form[0].reset();
			$(this).find('div.alert').hide();
		});

		$("#incomingTransferModal").on("submit", "form", function(e){
			e.preventDefault();
			that.transferIn(e);
		});

		$("#outgoingTransferModal").on("show.bs.modal", function(e) {
			var button = $(e.relatedTarget) // Button that triggered the modal
			var url = button.data('href');
			var form = $(this).find("form");
			form.attr("action", url);
			form[0].reset();
			$(this).find('div.alert').hide();
		});

		$("#outgoingTransferModal").on("submit", "form", function(e){
			e.preventDefault();
			that.transferOut(e);
		});
	},

	listTransactions: function()
	{
		var url = $('a', $('#accounts li.active')).attr('href');
		$.ajax({
			url: url,
			method: 'GET',
			dataType: 'json'
		})
		.done(this.onListTransactionsCompleted.bind(this));
	},

	depositCredits: function(e)
	{
		var modal = $(e.delegateTarget);
		var jform = $(e.target);
		var that = this;
		$.ajax({
			url: jform.attr('action'),
			method: 'POST',
			data: jform.serialize(),
			success: function() {
				that.listTransactions();
				modal.modal('hide');
			},
			statusCode: {
				400 : function(jqHXR, textStatus, errorThrown){
					json = $.parseJSON(jqHXR.responseText);
					if(json.description !== undefined) {
						that.show(modal, 'warning', json.description);
					}
					if(json.errors !== undefined) {
						that.show(modal, 'warning', json.errors[0].message);
					}
				}
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show(modal, 'danger', 'An unknown error "' + jqHXR.status + '" occurred');
			}
		});
	},

	withdrawCredits: function(e)
	{
		var modal = $(e.delegateTarget);
		var jform = $(e.target);
		var that = this;
		$.ajax({
			url: jform.attr('action'),
			method: 'POST',
			data: jform.serialize(),
			success: function() {
				that.listTransactions();
				modal.modal('hide');
			},
			statusCode: {
				400 : function(jqHXR, textStatus, errorThrown){
					json = $.parseJSON(jqHXR.responseText);
					if(json.description !== undefined) {
						that.show(modal, 'warning', json.description);
					}
					if(json.errors !== undefined) {
						that.show(modal, 'warning', json.errors[0].message);
					}
				}
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show(modal, 'danger', 'An unknown error "' + jqHXR.status + '" occurred');
			}
		});
	},

	transferIn: function(e)
	{
		var modal = $(e.delegateTarget);
		var jform = $(e.target);
		var that = this;

		$.ajax({
			url: jform.attr('action'),
			method: 'POST',
			data: jform.serialize(),
			success: function() {
				that.listTransactions();
				modal.modal('hide');
			},
			statusCode: {
				400 : function(jqHXR, textStatus, errorThrown){
					json = $.parseJSON(jqHXR.responseText);
					if(json.description !== undefined) {
						that.show(modal, 'warning', json.description);
					}
					if(json.errors !== undefined) {
						that.show(modal, 'warning', json.errors[0].message);
					}
				}
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show(modal, 'danger', 'An unknown error "' + jqHXR.status + '" occurred');
			}
		});
	},

	transferOut: function(e)
	{
		var modal = $(e.delegateTarget);
		var jform = $(e.target);
		var that = this;

		$.ajax({
			url: jform.attr('action'),
			method: 'POST',
			data: jform.serialize(),
			success: function() {
				that.listTransactions();
				modal.modal('hide');
			},
			statusCode: {
				400 : function(jqHXR, textStatus, errorThrown){
					json = $.parseJSON(jqHXR.responseText);
					if(json.description !== undefined) {
						that.show(modal, 'warning', json.description);
					}
					if(json.errors !== undefined) {
						that.show(modal, 'warning', json.errors[0].message);
					}
				}
			},
			error: function(jqHXR, textStatus, errorThrown) {
				that.show(modal, 'danger', 'An unknown error "' + jqHXR.status + '" occurred');
			}
		});
	},

	onListTransactionsCompleted: function(json)
	{
		var container = $('#account');

		var c = $('.dropdown-menu').empty();
		var d = $('.dropup').hide();
		if(json._links['ora:deposit'] !== undefined) {
			c.append('<li><a href="#" data-toggle="modal" data-target="#depositModal" data-href="' + json._links['ora:deposit'].href + '">Deposit</a></li>');
			d.show();
		}
		if(json._links['ora:withdrawal'] !== undefined) {
			c.append('<li><a href="#" data-toggle="modal" data-target="#withdrawalModal" data-href="' + json._links['ora:withdrawal'].href + '">Withdrawal</a></li>');
			d.show();
		}
		if(json._links['ora:incoming-transfer'] !== undefined) {
			c.append('<li><a href="#" data-toggle="modal" data-target="#incomingTransferModal" data-href="' + json._links['ora:incoming-transfer'].href + '">Incoming Transfer</a></li>');
			d.show();
		}
		if(json._links['ora:outgoing-transfer'] !== undefined) {
			c.append('<li><a href="#" data-toggle="modal" data-target="#outgoingTransferModal" data-href="' + json._links['ora:outgoing-transfer'].href + '">Outgoing Transfer</a></li>');
			d.show();
		}

		c = container.find('tbody').empty();
		var top = $('#actual-balance');
		top.text(0);
		var bottom = $('#starting-balance');

		var balance = 0;

		$.each(json.transactions, function(key, transaction) {
			var transactionDate = new Date(Date.parse(transaction.date));
			if(key == 0) { top.text(transaction.balance); }
			var cssClass = transaction.amount < 0 ? 'text-danger' : '';
			var source = '';
			switch (transaction.type) {
				case 'Withdrawal':
					source = 'Withdrawal by ' + transaction.payee;
					break;
				case 'IncomingTransfer':
					source = 'Incoming Transfer from ' + transaction.payer;
					break;
				case 'OutgoingTransfer':
					source = 'Outgoing Transfer to ' + transaction.payee;
					break;
				case 'Deposit':
					source = 'Deposit by ' + transaction.payer;
					break;
				default :
					source = transaction.type;
			}
			c.append('<tr><td>' + transactionDate.toLocaleString() + '</td><td>' + source + '</td><td>' + transaction.description + '</td><td class=' + cssClass + '>' + transaction.amount + '</td></tr>');
			balance = transaction.balance - transaction.amount;
		});
		bottom.text(balance);
		if(json.transactions.length == 0) {
			c.append('<tr><td colspan="4">No transactions in your history</td></tr>');
		}

		container.show();
	},

	show: function(container, level, message) {
		var alertDiv = container.find('div.alert');
		alertDiv.removeClass();
		alertDiv.addClass('alert alert-' + level);
		alertDiv.text(message);
		alertDiv.show();
	}
}

$().ready(function(e){
	$('#firstLevelMenu li').eq(1).addClass('active');
	accounting = new Accounting();
	accounting.listTransactions();
});