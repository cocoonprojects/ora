var Profile = function() {
};

Profile.prototype = {

	constructor : Profile,
	classe : 'Profile',
	data : [],

	loadUserDetail : function(url) {
		that = this;

		var _url = window.location.protocol+"//"+window.location.host+url;

		$.ajax({
			url : _url,
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

		$('#photo').attr('src', json.picture);
		$('#name').html(json.firstname + " " + json.lastname);
		$('#email').html(json.email);

		if (json.birthday == null) {
			$('#birthday').html("No Birthday Available");
		} else {
			$('#birthday').html(json.Birthday);// TODO Use this parameter's name
		}

		if (json.description == null) {
			$('#description').html("No User Profile Description Available");
		} else {
			$('#description').html(json.Birthday);// TODO Use this parameter's name
		}

		var membData = json._embedded['ora:organization-membership'];
		$('#orgMembership').html(membData.role + " of " + membData.organization.name);
		
		var creditsData = json._embedded['credits'];
		//Generated credits Table
		$('#tdOrg').html(membData.organization.name);
		$('#tdTotal').html(creditsData.total);
		$('#tdAvailable').html(creditsData.balance);
		
		//Credit Account History
		$('#tdLast3Month').html(creditsData.last3M);
		$('#tdLast6Month').html(creditsData.last6M);
		$('#tdLastYear').html(creditsData.lastY);

		container.show();
	}
};

$().ready(function(e) {
	var googleID = sessionStorage.googleid;
	$('head').append( '<meta name="google-signin-client_id" content="'+googleID+'">' );
	profile = new Profile();
	$('#profile-content').hide();
	var elem = document.getElementById("profile-content");
	var orgId = elem.getAttribute("org-id");
	var userId = elem.getAttribute("user-id");
	var url = "/"+orgId+"/user-profiles/"+userId;
	profile.loadUserDetail(url);

});