//
// Legacy Javascript file to selectively load the WPMUDEV Pop Up
//
// Written by Barry (Incsub)
//

// Enable us to get some cookie information - from http://stackoverflow.com/questions/5639346/shortest-function-for-reading-a-cookie-in-javascript
function po_get_cookie(name) {

	var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function po_createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function po_removeMessageBoxForever() {
	jQuery('#darkbackground').remove();
	jQuery(this).parents( '#' + popover.divname ).remove();
	po_createCookie('popover_never_view', 'hidealways', 365);
	return false;
}

function po_removeMessageBox() {
	jQuery('#darkbackground').remove();
	jQuery(this).parents( '#' + popover.divname ).remove();
	return false;
}

function po_loadMessageBox( ) {

	if( popover.usejs == 'yes' ) {

		jQuery('#' + popover.divname ).width(jQuery('#message').width());
		jQuery('#' + popover.divname ).height(jQuery('#message').height());

		jQuery('#' + popover.divname ).css('top', (jQuery(window).height() / 2) - (jQuery('#message').height() / 2) );
		jQuery('#' + popover.divname).css('left', (jQuery(window).width() / 2) - (jQuery('#message').width() / 2) );
	}

	jQuery('#' + popover.divname ).css('visibility', 'visible');
	jQuery('#darkbackground').css('visibility', 'visible');

	jQuery('#clearforever').click(po_removeMessageBoxForever);
	jQuery('#closebox').click(po_removeMessageBox);

	jQuery('#message').hover( function() {jQuery('.claimbutton').removeClass('hide');}, function() {jQuery('.claimbutton').addClass('hide');});

}

function po_load_popover() {

	window.setTimeout( po_loadMessageBox, popover.delay );

}


// The main function
function po_selectiveLoad() {

	po_load_popover();

}

// Call when the page is fully loaded
jQuery(window).load(po_selectiveLoad);