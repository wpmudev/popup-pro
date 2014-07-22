/*
 * Javascript file to selectively load the WPMUDEV Pop Up
 */

var po_adminajax = popover_load_custom.admin_ajax_url;

// Enable us to get some cookie information - from http://stackoverflow.com/questions/5639346/shortest-function-for-reading-a-cookie-in-javascript
function po_get_cookie(name) {

    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function po_createCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else
        var expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
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

    if (data['html'] != '') {
        // Set the name for other functions to use
        popovername = '#' + data['name'];

        jQuery('<style type="text/css">' + data['style'] + '</style>').appendTo('head');
        jQuery(data['html']).appendTo('body');

        if (data['usejs'] == 'yes') {

            jQuery('#' + data['name']).width(jQuery('#message').width());
            jQuery('#' + data['name']).height(jQuery('#message').height());

            jQuery('#' + data['name']).css('top', (jQuery(window).height() / 2) - (jQuery('#message').height() / 2));
            jQuery('#' + data['name']).css('left', (jQuery(window).width() / 2) - (jQuery('#message').width() / 2));
        }

        jQuery('#' + data['name']).css('visibility', 'visible');
        jQuery('#darkbackground').css('visibility', 'visible');

        jQuery('#clearforever').click(po_removeMessageBoxForever);
        jQuery('#closebox').click(po_removeMessageBox);

        jQuery('#message').hover(function() {
            jQuery('.claimbutton').removeClass('hide');
        }, function() {
            jQuery('.claimbutton').addClass('hide');
        });
    }

}

function po_onsuccess(data) {
    // set the data to be a global variable so we can grab it after the timeout
    mydata = data;

    if (data['name'] != 'nopoover') {
        window.setTimeout(po_loadMessageBox, data['delay']);
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

    jQuery.ajax({
        url: theajax,
        dataType: 'jsonp',
        jsonpCallback: 'po_onsuccess',
        data: {action: 'popover_selective_ajax',
            thefrom: thefrom.toString(),
            thereferrer: thereferrer.toString(),
            active_popover: force_popover.toString()
        },
        success: function(data) {

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