$(document).on("click","#login-auth", function(){
	$('#popupLogin').modal('show');
});

$(document).on("click","a.link-auth", function(){
	//popup = window.open('http://www.google.co.in'); 
});

if(opener != null)
{
	opener.location.reload(true);
	self.close();
}
