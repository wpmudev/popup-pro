//
// Javascript file to selectively load the WPMUDEV Pop Up
//
// Written by Barry (Incsub)
//
//
//

// Where the admin-ajax.php file is relative to the domain - have to hardcode for now due to this being a JS file
var po_adminajax = '/wp-admin/admin-ajax.php';

// Get the source of this file so we know where we are - from http://stackoverflow.com/questions/984510/what-is-my-script-src-url/984656#984656
var po_scriptSource = (function(scripts) {
    var scripts = document.getElementsByTagName('script'),
        script = scripts[scripts.length - 1];

    if (script.getAttribute.length !== undefined) {
        return script.src
    }

    return script.getAttribute('src', -1)
}());

// Gets the domain part of the url
function po_get_domain( theurl ) {

	pathArray = theurl.split( '/' );
	return 'http://' + pathArray[2];

}

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

function po_showMessageBox( data ) {

}

function po_onsuccess( data ) {

	//window.setTimeout( "po_showMessageBox( data )", data['delay'] );
	jQuery( '<style type="text/css">' + data['style'] + '</style>' ).appendTo('head');
	jQuery( data['html'] ).appendTo('body');

	if( data['usejs'] == 'yes' ) {

		jQuery('#' + data['name']).width(jQuery('#message').width());
		jQuery('#' + data['name']).height(jQuery('#message').height());

		jQuery('#' + data['name']).css('top', (jQuery(window).height() / 2) - (jQuery('#message').height() / 2) );
		jQuery('#' + data['name']).css('left', (jQuery(window).width() / 2) - (jQuery('#message').width() / 2) );
	}

}

function po_load_popover() {

	var thedomain = po_get_domain(po_scriptSource);
	var theajax = thedomain + po_adminajax;
	var thefrom = window.location;
	var thereferrer = document.referrer;

	jQuery.ajax( {
		url : theajax,
		dataType : 'jsonp',
		jsonpCallback : 'po_onsuccess',
		data : {	action : 'popover_selective_ajax',
					thefrom : thefrom.toString(),
					thereferrer : thereferrer.toString()
				},
		success : function(data) {

		  		}
		}
		);

	return false;
}


// The main function
function po_selectiveLoad() {

	po_load_popover();


	//jQuery('#clearforever').click(removeMessageBoxForever);
	//jQuery('#closebox').click(removeMessageBox);

	//jQuery('#message').hover( function() {jQuery('.claimbutton').removeClass('hide');}, function() {jQuery('.claimbutton').addClass('hide');});

	//window.setTimeout( showMessageBox, popover.messagedelay );

}

// Call when the page is fully loaded
jQuery(window).load(po_selectiveLoad);