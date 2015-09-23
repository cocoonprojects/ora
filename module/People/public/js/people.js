var People = function(){
	
	var defaultPageSize = 20,
		pageSize = defaultPageSize,
		nextPageSize = defaultPageSize,
		pageOffset = 0;
	
	this.getPageSize = function(){
		return pageSize;
	};
	this.setPageSize = function(size){
		pageSize = size;
	};
	this.getNextPageSize = function(){
		return nextPageSize;
	};
	this.getPageOffset = function(){
		return pageOffset;
	};
	this.setPageOffset = function(offset){
		pageOffset = offset;
	};
	this.resetPageSize = function(){
		pageSize = defaultPageSize;
	}
	
	var pollingFrequency = 10000;
	this.pollingObject = this.setupPollingObject(pollingFrequency, this.loadPeople);
	
	this.bindEventsOn();
};

People.prototype = {

	constructor: People,
	classe : 'People',
	data : [],

	bindEventsOn: function()
	{
		var that = this;
		
		$("body").on("click", "a[data-action='nextPage']", function(e){
			e.preventDefault();
			that.loadMorePeople(e);
		});
	},
	
	loadPeople: function(url)
	{
		var that = this;
		var u = this.getPageOffset() > 0 ? url+'?offset='+that.getPageOffset()+'&limit='+that.getPageSize() : url+'?limit='+that.getPageSize();
		$.ajax({
			url: u,
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
			if(this.data._links !== undefined && this.data._links["next"] !== undefined) {
				var limit = this.getPageSize() + this.getNextPageSize();
				var offset = this.getPageOffset();
				container.append(
					'<div class="text-center">' +
							'<a rel="next" href="'+this.data._links["next"]["href"]+'?offset=' + offset + '&limit=' + limit + '" data-action="nextPage">More</a>' +
					'</div>');
			}
		}
	},
	
	loadMorePeople: function(e){
		var url = $(e.target).attr('href');
		var that = this;
		$.ajax({
			url: url,
			headers: {
				'GOOGLE-JWT': sessionStorage.token
			},
			method: 'GET',
			beforeSend: that.pollingObject.stopPolling.bind(that.pollingObject)(),
		}).done(function(json){
			that.setPageSize(json.count);
			that.onLoadPeopleCompleted.bind(that, json)();
		}).always(function(){
			that.pollingObject.startPolling.bind(that.pollingObject)();
		});
	},
	
	setupPollingObject: function(frequency, pollingFunction){
		
		var that = this;
		
		return {
			pollID: 0,
			startPolling: function(){
				this.pollID = setInterval(pollingFunction.bind(that, $("#people-home").attr('href')+'/members'), frequency);
			},
			stopPolling: function(){
				return clearInterval(this.pollID);
			}
		};
	}
};

$().ready(function(e){
	people = new People();
	people.loadPeople($("#people-home").attr('href')+'/members');
	people.pollingObject.startPolling();
});