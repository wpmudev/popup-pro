<?php
/*
* Popover selective loading file - had to move from JS to PHP because we needed some extra processing that couldn't be handled in a JS file :(
*/
// Header settings code based on code from load-scripts.php
error_reporting(0);

$compress = ( isset($_GET['c']) && $_GET['c'] );
$force_gzip = ( $compress && 'gzip' == $_GET['c'] );
$expires_offset = 31536000;
$out = '';

header('Content-Type: application/x-javascript; charset=UTF-8');
header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + $expires_offset ) . ' GMT');
header("Cache-Control: public, max-age=$expires_offset");

if ( $compress && ! ini_get('zlib.output_compression') && 'ob_gzhandler' != ini_get('output_handler') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
	header('Vary: Accept-Encoding'); // Handle proxies
	if ( false !== stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') && function_exists('gzdeflate') && ! $force_gzip ) {
		header('Content-Encoding: deflate');
		$out = gzdeflate( $out, 3 );
	} elseif ( false !== stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && function_exists('gzencode') ) {
		header('Content-Encoding: gzip');
		$out = gzencode( $out, 3 );
	}
}

define( 'POPOVERJSLOCATION', dirname($_SERVER["SCRIPT_FILENAME"]) );
define( 'ABSPATH', dirname(dirname(dirname(POPOVERJSLOCATION))) . '/' );
define( 'WPINC', 'wp-includes' );

// Load WordPress so that we can get some bits and pieces.
require_once( ABSPATH . 'wp-load.php');

?>
//
// Javascript file to selectively load the WPMUDEV Pop Up
//
// Written by Barry (Incsub)
//
//
//

// Where the admin-ajax.php file is relative to the domain - have to hardcode for now due to this being a JS file
var po_adminajax = '<?php echo admin_url( 'admin-ajax.php' ); ?>';

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
	jQuery(this).parents(popovername).remove();
	po_createCookie('popover_never_view', 'hidealways', 365);
	return false;
}

function po_removeMessageBox() {
	jQuery('#darkbackground').remove();
	jQuery(this).parents(popovername).remove();
	return false;
}

function po_loadMessageBox( ) {

	// move the data back to the data variable, from mydata so we can use it without changing a chunk of code :)
	data = mydata;

	if( data['html'] != '' ) {
		// Set the name for other functions to use
		popovername = '#' + data['name'];

		jQuery( '<style type="text/css">' + data['style'] + '</style>' ).appendTo('head');
		jQuery( data['html'] ).appendTo('body');

		if( data['usejs'] == 'yes' ) {

			jQuery('#' + data['name']).width(jQuery('#message').width());
			jQuery('#' + data['name']).height(jQuery('#message').height());

			jQuery('#' + data['name']).css('top', (jQuery(window).height() / 2) - (jQuery('#message').height() / 2) );
			jQuery('#' + data['name']).css('left', (jQuery(window).width() / 2) - (jQuery('#message').width() / 2) );
		}

		jQuery('#' + data['name']).css('visibility', 'visible');
		jQuery('#darkbackground').css('visibility', 'visible');

		jQuery('#clearforever').click(po_removeMessageBoxForever);
		jQuery('#closebox').click(po_removeMessageBox);

		jQuery('#message').hover( function() {jQuery('.claimbutton').removeClass('hide');}, function() {jQuery('.claimbutton').addClass('hide');});
	}

}

function po_onsuccess( data ) {
	// set the data to be a global variable so we can grab it after the timeout
	mydata = data;

	if(data['name'] != 'nopoover') {
		window.setTimeout( po_loadMessageBox, data['delay'] );
	}

}

function po_load_popover() {

	var theajax = po_adminajax;
	var thefrom = window.location;
	var thereferrer = document.referrer;

	// Check if we are forcing a popover - if not then set it to a default value of 0
	if (typeof force_popover === 'undefined') {
		force_popover = 0;
	}

	jQuery.ajax( {
		url : theajax,
		dataType : 'jsonp',
		jsonpCallback : 'po_onsuccess',
		data : {	action : 'popover_selective_ajax',
					thefrom : thefrom.toString(),
					thereferrer : thereferrer.toString(),
					active_popover : force_popover.toString()
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

}

// Call when the page is fully loaded
jQuery(window).load(po_selectiveLoad);

<?php
exit;
?>