function redirectToLogin() {
	sessionStorage.setItem('redirectURL', window.location);
	window.location = '/';
}