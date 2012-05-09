function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function removeMessageBoxForever() {
	jQuery('#darkbackground').remove();
	jQuery(this).parents(popover.messagebox).remove();
	createCookie('popover_never_view', 'hidealways', 365);
	return false;
}

function removeMessageBox() {
	jQuery('#darkbackground').remove();
	jQuery(this).parents(popover.messagebox).remove();
	return false;
}

function showMessageBox() {
	jQuery(popover.messagebox).css('visibility', 'visible');
	jQuery('#darkbackground').css('visibility', 'visible');
}

function newShowMessageBox() {



}

function boardReady() {
	jQuery('#clearforever').click(removeMessageBoxForever);
	jQuery('#closebox').click(removeMessageBox);

	jQuery('#message').hover( function() {jQuery('.claimbutton').removeClass('hide');}, function() {jQuery('.claimbutton').addClass('hide');});

	window.setTimeout( showMessageBox, popover.messagedelay );

}

jQuery(window).load(boardReady);