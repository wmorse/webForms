        function checkPWsame(){
        var thisForm = document.forms[0];
        var pw =thisForm.password.value;
        var pwII =thisForm.passwordtwo.value;
        var userName =thisForm.username.value;
        var userNameII =thisForm.usernametwo.value;
        var submitval = "Create Account";
              var writeGroup = "";
              var returnVal  = true;
              if (pw!=pwII) {
               writeGroup += "Password does not  match, please try again\n";
                 returnVal=  false;
              }
              if (userName!=userNameII) {
                  writeGroup += "Username (e-mail) does not  match, please try again\n";
                    returnVal=  false;
                 }
              if (thisForm.username.value.length <1) {
                 writeGroup += "Please fill in the username field\n";
                 returnVal=  false;
              }
			  if (thisForm.username.value.indexOf(' ') >= 0){
				 writeGroup += "Please enter a username without spaces\n";
				 returnVal=  false;
			  }			  
              if (thisForm.password.value.length <1) {
                 writeGroup += "Please fill in the password field\n";
                 returnVal=  false;
              }
              if (thisForm.userFirstName.value.length <1) {
                 writeGroup += "Please fill in the First Name field\n";
                 returnVal=  false;
              }
              if (thisForm.userLastName.value.length <1){
                 writeGroup += "Please fill in the Last Name field\n";
                 returnVal=  false;
              }
              if (thisForm.username.value.length >0) {
                   if (thisForm.username.value.indexOf(' ') >= 0){
                        writeGroup += "email addresses cannot have spaces in them\n";
                        returnVal=  false;
                   }
	               else if (thisForm.username.value.indexOf('@') == -1){
        	                     writeGroup += "a valid email address must have an @ in it\n";
        	                     returnVal=  false;
         	       }
              }else{
                 writeGroup += "Please fill in the username field with your valid e-mail address\n";
                 returnVal=  false;
              }
              if (!returnVal){
               alert(writeGroup);
              }
        return returnVal;
        }
function APCookie () {
var allcookies;
allcookies = document.cookie;
var OldAPCookieValue = 0;
    if (allcookies == "") {
	OldAPCookieValue = 0;
	}
    else {
	var prefix = "APCookie=";
	var start = allcookies.indexOf(prefix);
		if (start == -1) {
	    OldAPCookieValue = 0;
	    }
		else {
	    start += prefix.length;
	    var just_past_end = allcookies.indexOf (';', start);
	    	if (just_past_end == -1) {
			just_past_end = allcookies.length;
			}
	    // The strange arithmetic is used to force a conversion from string to number.
	    OldAPCookieValue = allcookies.substring (start, just_past_end) - 1 + 1;
	    }
	}
return (OldAPCookieValue);
}

function checkCookies(statusIndex){
var OldValue;
var SetValue;
var NewValue;
OldValue = APCookie ();
SetValue = OldValue + 1;
document.cookie = "StatusCookie=" + SetValue;
NewValue = APCookie ();
var isCookies = (NewValue != OldValue);
var thisHiddenElem = 'status[' + statusIndex + ']';
document.forms[0][thisHiddenElem].value = (isCookies == true) ? 1 : 0;
}