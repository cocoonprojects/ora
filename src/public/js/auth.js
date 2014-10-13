var Auth = function(){

	var popup = null;
	
	$(document).on("click","#login-auth", function(){
		$('#popupLogin').modal('show');
	});
}

Auth.prototype = {
		
	openAuthWindow: function(url)	
	{
		this.popup = window.open(url,'1','width=700,height=500,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0');			
		
	}
}

auth = new Auth();