/*global jQuery:false */
/*global window:false */
/*global document:false */
/*global IncPopup:false */

;(function () {
IncPopup.po_status = 'closed';
IncPopup.po_queue = [];
IncPopup.po_current = null;
IncPopup.recent_ajax_calls = [];
IncPopup.doing_ajax = false;

/**
 * Either opens the PopUp or enqueues it to be opened as soon as the current
 * PopUp is closed.
 */
function popup_open( event, popup ) {
	if ( 'closed' === IncPopup.po_status ) {
		if ( popup._show() ) {
			IncPopup.po_current = popup;
			IncPopup.po_status = 'open';
		} else {
			// The PopUp could not be opened due to some error...
			popup_close( null, popup );
		}
	} else {
		IncPopup.po_queue[IncPopup.po_queue.length] = popup;
	}
}
jQuery( document ).on('popup-open', popup_open);

/**
 * Marks the specified PopUp as closed and opens the next enqueued PopUp.
 */
function popup_close( event, popup ) {
	IncPopup.po_status = 'closed';
	IncPopup.po_current = null;

	// PopUp was closed, check if there is another PopUp in open-queue.
	if ( IncPopup.po_queue.length > 0 ) {
		var item = IncPopup.po_queue.shift();
		popup_open( null, item );
	}
}
jQuery( document ).on('popup-closed', popup_close);

/**
 * Load popup data via ajax.
 * This function will create new IncPopup objects.
 */
function load_popups( event, options, id, data ) {
	var ajax_args, ajax_data,
		po_id = 0,
		thefrom = str_reverse( window.location.toString() ),
		thereferrer = str_reverse( document.referrer.toString() ),
		the_data = null;

	var handle_done = function handle_done( data ) {
		the_data = jQuery.extend( {}, options );
		the_data.popup = data;

		// This will create new IncPopup objects.
		initialize( the_data );
	};

	// Legacy: force_popover = load a popup_id by ID.
	if ( window.force_popover ) {
		po_id = window.force_popover.toString();
	}

	// New way of specifying popup ID is via param: load(id)
	if ( id !== undefined ) {
		po_id = id.toString();
	}

	options['ajax_data'] = options['ajax_data'] || {};
	ajax_data = jQuery.extend( {}, options['ajax_data'] );

	ajax_data['action']      = 'inc_popup';
	ajax_data['do']          = options['do'];
	ajax_data['thefrom']     = thefrom;
	ajax_data['thereferrer'] = thereferrer;

	if ( po_id ) { ajax_data['po_id'] = po_id; }
	if ( data )  { ajax_data['data'] = data; }
	if ( options['preview'] ) { ajax_data['preview'] = true; }

	ajax_args = {
		url:           options['ajaxurl'],
		dataType:      'jsonp',
		jsonpCallback: 'po_data',
		data:          ajax_data,
		success: function( data ) {
			handle_done( data );
		},
		complete: function() {
			jQuery( document ).trigger( 'popup-load-done', [the_data] );
		}
	};

	return jQuery.ajax(ajax_args);
}
jQuery( document ).on( 'popups-load', load_popups );

/**
 * Create and initialize new IncPopup objects
 */
function initialize( popup_data ) {
	if ( ! popup_data ) { return; }

	// Initlize a single PopUp.
	var spawn_popup = function spawn_popup( data ) {
		if ( ! data ) { return; }

		// Insert the PopUp CSS and HTML as hidden elements into the DOM.
		// PopUp is hidden here, because displaying is linked to some condition.
		if ( data.popup && data.popup['html'] ) {
			jQuery( '<style type="text/css">' + data.popup['styles'] + '</style>' )
				.appendTo('head');
		}

		// inc_popup .. the most recently created Popup object.
		window.inc_popup = new IncPopup( data );

		// inc_popups .. collection of all Popup objects on current page.
		window.inc_popups[window.inc_popups.length] = window.inc_popup;

		// Fires when the popup is fully initialized;
		// On admin we use this event to display the preview.
		jQuery( document ).trigger( 'popup-initialized', [window.inc_popup] );

		if ( data['noinit'] || data['preview'] ) { return; }
		window.inc_popup.init();
	};

	// Initialize a single or multiple PopUps, depending on provided data.
	if ( popup_data.popup instanceof Array ) {
		for ( var i = 0; i < popup_data.popup.length; i += 1 ) {
			var data = jQuery.extend( {}, popup_data );
			data.popup = popup_data.popup[i];
			spawn_popup( data );
		}
	} else if ( popup_data instanceof Object ) {
		spawn_popup( popup_data );
	}
}

// Reverse a string preserving utf characters
function str_reverse( str ) {
	var charArray = [];

	for (var i = 0; i < str.length; i++) {
		if ( i + 1 < str.length ) {
			var value = str.charCodeAt( i );
			var nextValue = str.charCodeAt(i+1);
			if ( ( value >= 0xD800 && value <= 0xDBFF &&
				(nextValue & 0xFC00) === 0xDC00) || // Surrogate pair
				(nextValue >= 0x0300 && nextValue <= 0x036F) // Combining marks
			) {
				charArray.unshift( str.substring(i, i+2) );
				i++; // Skip the other half
				continue;
			}
		}

		// Otherwise we just have a rogue surrogate marker or a plain old character.
		charArray.unshift( str[i] );
	}

	return charArray.join( '' );
}

// Store flag whether ajax request is processed.
jQuery( document ).ajaxStart(function(ev) {
	IncPopup.doing_ajax = true;
});

// Store all Ajax responses.
jQuery( document ).ajaxComplete(function(ev, jqXHR, settings) {
	IncPopup.doing_ajax = false;
	IncPopup.recent_ajax_calls.unshift( jqXHR );
});

// Initialize the PopUp once the page is loaded.
jQuery(function() {
	window.inc_popups = [];

	if ( window._popup_data ) {
		initialize( window._popup_data );
	}
});
})();
