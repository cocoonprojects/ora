var Profile = function() {
};

Profile.prototype = {

	constructor : Profile,
	classe : 'Profile',
	data : [],

	loadUserDetail : function(url) {
		that = this;

		//var userId = $('#profile-content').attr("user-id");
		var url = document.URL + "/details";

		$.ajax({
			url : url,
			headers : {
				'GOOGLE-JWT' : sessionStorage.token
			},
			method : 'GET',
			data : {
				//id : userId
			}
		}).done(that.onLoadUserProfileCompleted.bind(this));
	},

	onLoadUserProfileCompleted : function(json) {
		var container = $('#profile-content');

		$('#photo').attr('src', json.Avatar);
		$('#name').html(json.Firstname + " " + json.Lastname);
		$('#email').html(json.Email);

		if (json.Birthday == null) {
			$('#birthday').html("No Birthday Available");
		} else {
			$('#birthday').html(json.Birthday);// TODO Use this parameter's name
		}

		if (json.Description == null) {
			$('#description').html("No User Profile Description Available");
		} else {
			$('#description').html(json.Birthday);// TODO Use this parameter's name
		}

		$('#orgMembership').html(json.MemberRole + " of " + json.OrgName);
		
		//Generated credits Table
		$('#tdOrg').html(json.OrgName);
		$('#tdTotal').html(json.TotGenCredits);
		$('#tdAvailable').html(json.ActualBalance);
		
		//Credit Account History
		$('#tdLast3Month').html(json.Last3MonthCredits);
		$('#tdLast6Month').html(json.Last6MonthCredits);
		$('#tdRestOfYear').html(json.RestOfTheYearCredits);

		container.show();
	}
};

$().ready(function(e) {

	profile = new Profile();
	var elem = document.getElementById("profile-content");
	$('#profile-content').hide()
	var url = "user-profile-detail/" + elem.getAttribute("user-id");
	profile.loadUserDetail(url);

});