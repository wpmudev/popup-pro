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
	jQuery(this).parents(popover.messagebox).removeClass('visiblebox').addClass('hiddenbox');
	jQuery(this).parents('#darkbackground').addClass('hide');
	createCookie('popover_never_view', 'hidealways', 365);
	return false;
}

function removeMessageBox() {
	jQuery(this).parents(popover.messagebox).removeClass('visiblebox').addClass('hiddenbox');
	jQuery(this).parents('#darkbackground').addClass('hide');
	return false;
}

function boardReady() {
	jQuery('#clearforever').click(removeMessageBoxForever);
	jQuery('#closebox').click(removeMessageBox);

	jQuery('#message').hover( function() {jQuery('.claimbutton').removeClass('hide');}, function() {jQuery('.claimbutton').addClass('hide');});
	jQuery(popover.messagebox).css('visibility', 'visible');
	jQuery('#darkbackground').css('visibility', 'visible');
}

jQuery(window).load(boardReady);