function account_create_validate() {
	if (document.AccountForm.username.value === "") {
		alert("Type a username for your account");
		document.AccountForm.username.focus();
		return false;
	}
	if (document.AccountForm.email.value === "") {
		alert("Type an email for your account");
		document.AccountForm.email.focus();
		return false;
	}
	if (!(document.AccountForm.email.value.includes("@")) || !(document.AccountForm.email.value.includes("."))
		|| document.AccountForm.email.value.indexOf("@") === 0 || document.AccountForm.email.value.indexOf("@") < document.AccountForm.email.value.indexOf(".")) {
		alert("Please provide a valid e-mail address!");
		document.AccountForm.email.focus();
		return false;
	}
	if (document.AccountForm.password.value === "") {
		alert("Type a password for your account");
		document.AccountForm.email.focus();
		return false;
	}
}

function account_retrieve_validate() {
	if (document.UserForm.username.value === "") {
		alert("Type your username to access your account");
		document.UserForm.username.focus();
		return false;
	}
	if (document.UserForm.email.value === "") {
		alert("Please include your email for verification");
		document.UserForm.email.focus();
		return false;
	}
	return true;
}

function create_playlist_validate() {
	if (document.CreateForm.create.value === "") {
		alert("Make sure to name your playlist!");
		document.CreateForm.create.focus();
		return false;
	}
	if (document.CreateForm.track1.value === "" && document.CreateForm.track2.value === ""
		&& document.CreateForm.track3.value === "" && document.CreateForm.track4.value === ""
		&& document.CreateForm.track5.value === "") {
		alert("A playlist must include at least one song");
		document.CreateForm.track1.focus();
		return false;
	}
	return true;
}

function retrieve_playlist_validate() {
	if (document.RetrieveForm.retrieve.value === "") {
		alert("Type the name of the playlist you wish to retrieve");
		document.RetrieveForm.retreive.focus();
		return false;
	}
	return true;
}
