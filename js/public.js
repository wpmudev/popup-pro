/*! PopUp - v4.8.0
 * http://premium.wpmudev.org/project/the-pop-over-plugin/
 * Copyright (c) 2016; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global wpmUi:false */
/*jslint evil: true */   // Allows us to keep the `fn = new Function()` line

;(function () {
window.IncPopup = function IncPopup( _options ) {
	var me = this;

	me.data = _options.popup;
	me.have_popup = false;
	me.ajax_data = {};
	me.opened = 0;
	me.popup = null;

	/**
	 * Close PopUp.
	 * Depending on the "multi_open" flag it can be opened again.
	 */
	me.close_popup = function close_popup() {
		jQuery( 'html' ).removeClass( 'can-scroll no-scroll' );

		if ( me.data.display_data.click_multi ) {
			me.popup.hide();
		} else {
			me.popup.destroy();
			me.have_popup = false;
		}

		jQuery( document ).trigger( 'popup-closed', [me, me.data] );
		// Legacy trigger.
		jQuery( document ).trigger( 'popover-closed', [me, me.data] );

		// Check if we should hide the popup "forever".
		if ( me.data.close_hide ) {
			var expiry = me.data.expiry || 365;
			if ( _options.preview ) {
				return false;
			}

			me.set_cookie( 'po_h', 1, expiry );
		}

		return false;
	};

	/**
	 * Permanently close the popup.
	 */
	me.close_popup_forever = function close_popup_forever() {
		me.data.close_hide = true;
		me.close_popup();
	};

	/**
	 * When user clicked on the background-layer.
	 */
	me.background_clicked = function background_clicked( ev ) {
		var el = jQuery( ev.target );

		if ( el.hasClass( 'wdpu-background' ) ) {
			if ( ! me.data.overlay_close ) { return; }
			me.close_popup();
		}
	};

	/**
	 * Resize and move the PopUp. Triggered when PopUp is loaded and
	 * window is resized.
	 */
	me.move_popup = function move_popup( wnd ) {
		var new_width, new_height,
			reduce_w_by = 0, reduce_h_by = 0,
			_el_reduce = null,
			_el_resize = wnd.find( '.resize' ),
			_el_move = wnd.find( '.move' ),
			_el_img = wnd.find( '.wdpu-image img' ),
			_el_wnd = wnd.find( '.popup' );

		// Resize, if custom-size is active.
		if ( me.data.custom_size && _el_resize.length ) {
			if ( me.data.height && ! isNaN( me.data.height ) ) {
				if ( _el_resize.data( 'reduce-height' ) ) {
					_el_reduce = jQuery( _el_resize.data( 'reduce-height' ) );
					reduce_h_by = _el_reduce.outerHeight();
				}
				new_height = me.data.height - reduce_h_by;
				if ( new_height < 100 ) { new_height = 100; }
				_el_resize.height( new_height );
			}

			if ( me.data.width && ! isNaN( me.data.width ) ) {
				if ( _el_resize.data( 'reduce-width' ) ) {
					_el_reduce = jQuery( _el_resize.data( 'reduce-width' ) );
					reduce_w_by = _el_reduce.outerWidth();
				}
				new_width = me.data.width - reduce_w_by;
				if ( new_width < 100 ) { new_width = 100; }
				_el_resize.width( new_width );
			}
		}

		// This function centers the PopUp and the featured image.
		var update_position = function update_position() {
			if ( ! _el_move.hasClass( 'no-move-x' ) ) {
				var win_width = jQuery( window ).width(),
					msg_width = _el_wnd.outerWidth(),
					msg_left = (win_width - msg_width) / 2;

				// Move window horizontally.
				if ( msg_left < 0 ) { msg_left = 0; }
				_el_move.css({ 'left': msg_left });
			}

			if ( ! _el_move.hasClass( 'no-move-y' ) ) {
				var win_height = jQuery( window ).height(),
					msg_height = _el_wnd.outerHeight(),
					msg_top = (win_height - msg_height) / 2;

				// Move window vertically.
				if ( msg_top < 0 ) { msg_top = 0; }
				_el_move.css({ 'top': msg_top });
			}

			// Move the image.
			if ( _el_img.length ) {
				var offset_x, offset_y,
					img_width = _el_img.width(),
					img_height = _el_img.height(),
					box_width = _el_img.parent().width(),
					box_height = _el_img.parent().height();

				// Center horizontally.
				if ( img_width > box_width ) {
					// Center image.
					offset_x = (box_width - img_width) / 2;
					_el_img.css({ 'margin-left': offset_x });
				} else {
					// Align image according to layout.
					_el_img.css({ 'margin-left': 0 });
				}

				// Center vertially.
				if ( img_height > box_height ) {
					// Center image.
					offset_y = (box_height - img_height) / 2;
					_el_img.css({ 'margin-top': offset_y });
				} else {
					// Align image according to layout.
					_el_img.css({ 'margin-top': 0 });
				}
			}
		};

		// Short delay before positioning the popup to give the browser time
		// to show/resize the popup (20ms ~ 1 screen refresh)
		window.setTimeout(update_position, 20);
		update_position();
	};

	/**
	 * Reject the current PopUp: Do not display it.
	 */
	me.reject = function reject() {
		me.have_popup = false;
		me.data = {};
	};

	/**
	 * Check if the PopUp is ready to be displayed.
	 * If it is ready then it is displayed.
	 */
	me.prepare = function prepare() {
		jQuery( document ).trigger( 'popup-init', [me, me.data] );

		if ( me.have_popup ) {
			switch ( me.data.display ) {
				case 'scroll':
					jQuery( window ).on( 'scroll', me.show_at_position );
					break;

				case 'anchor':
					jQuery( window ).on( 'scroll', me.show_at_element );
					break;

				case 'delay':
					var delay = me.data.display_data.delay * 1000;
					if ( 'm' === me.data.display_data.delay_type ) {
						delay *= 60;
					}

					window.setTimeout( me.show_popup, delay );
					break;

				default:
					// A custom action will show the PopUp (e.g. click/leave)
					window.setTimeout(function() {
						if ( 'function' === typeof me.custom_handler ) {
							me.custom_handler( me );
						}
					}, 20);
					break;
			}
		} else {
			// PopUp was rejected during popup-init event. Do not display.
		}
	};

	/**
	 * Observe the scroll-top to trigger the PopUp.
	 */
	me.show_at_position = function show_at_position( ev ) {
		var height, perc,
			el = jQuery( this ),
			top = el.scrollTop();

		if ( 'px' === me.data.display_data.scroll_type ) {
			if ( top >= me.data.display_data.scroll ) {
				jQuery( window ).off( 'scroll', me.show_at_position );
				me.show_popup();
			}
		} else { // this handles '%'
			height = jQuery( document ).height() - jQuery( window ).height();
			perc = 100 * top / height;

			if ( perc >= me.data.display_data.scroll ) {
				jQuery( window ).off( 'scroll', me.show_at_position );
				me.show_popup();
			}
		}
	};

	/**
	 * Tests if a specific HTML element is visible to trigger the PopUp.
	 * We intentionally calculate el_top every time this function is called
	 * because the element may be hidden or not present at page load.
	 */
	me.show_at_element = function show_at_element( ev ) {
		var anchor = jQuery( me.data.display_data.anchor ),
			view_top = jQuery( window ).scrollTop(),
			view_bottom = view_top + jQuery( window ).height(),
			el_top = anchor.offset().top,
			offset = view_bottom - el_top;

		// When 10px of the element are visible show the PopUp.
		if ( offset > 10 ) {
			jQuery( window ).off( 'scroll', me.show_at_element );
			me.show_popup();
		}
	};

	/**
	 * Can be used from custom_handler() to make a Popup visible.
	 */
	me.show_popup = function show_popup() {
		jQuery( document ).trigger( 'popup-open', [ me ] );

		// Prevent default action when user attached this to a click event.
		return false;
	};

	/**
	 * Display the PopUp!
	 * This function is called by popup_open() below!!!
	 */
	me._show = function _show() {
		var count,
			wnd_width = 'auto',
			wnd_height = 'auto';

		count = parseInt( me.get_cookie('po_c'), 10 );
		if ( isNaN( count ) ) { count = 0; }
		me.set_cookie( 'po_c', count + 1, 365 );

		if ( me.data.custom_size ) {
			wnd_width = me.data.width;
			wnd_height = me.data.height;
		}

		me.opened += 1;

		me.popup = wpmUi.popup( _options.popup.html, _options.popup.styles )
			.size( wnd_width, wnd_height )
			.animate( me.data.animation_in, me.data.animation_out )
			.onresize( me.move_popup )
			.on( 'click', '.wdpu-background', me.background_clicked )
			.on( 'click', '.wdpu-hide-forever', me.close_popup_forever )
			.on( 'click', '.wdpu-close', me.close_popup )
			.on( 'click', '.close', me.close_popup )
			.show()
		;

		if ( me.data.scroll_body ) {
			jQuery( 'html' ).addClass( 'can-scroll' );
		} else {
			jQuery( 'html' ).addClass( 'no-scroll' );
		}

		return true;
	};


	/*-----  Dynamically load PopUps  ------*/


	/**
	 * Load popup data via ajax.
	 */
	me.load_popup = function load_popup( id, data ) {
		if ( undefined === id && _options.preview ) {
			return;
		}

		me.have_popup = false; // This object cannot display a PopUp...
		jQuery( document ).trigger( 'popups-load', [ _options, id, data ] );
	};

	/**
	 * Handles clicks on the CTA button. This function can be overwritten
	 * via the popup.extend object to customize behavior.
	 */
	me.cta_click = function cta_click() {
		// Default: do nothing.
		return true;
	};

	/**
	 * A form inside the PopUp is submitted.
	 */
	me.form_submit = function form_submit( ev ) {
		var tmr_check, duration, frame, form = jQuery( this ),
			popup = form.parents( '.wdpu-container' ).first(),
			msg = popup.find( '.wdpu-msg' ),
			inp_popup = jQuery( '<input type="hidden" name="_po_method_" />' ),
			po_id = '.wdpu-' + me.data.popup_id,
			iteration;

		if ( ! popup.length ) { return true; }

		// Frequently checks the loading state of the hidden iframe.
		function check_state() {
			var is_done = false;

			if ( 'complete' === frame[0].contentDocument.readyState &&
				! IncPopup.doing_ajax
			) {
				is_done = true;
			} else {
				iteration += 1; // 20 iterations is equal to 1 second.

				// 200 iterations are 10 seconds.
				if ( iteration > 200 ) {
					jQuery( document ).trigger( 'popup-submit-timeout', [me, me.data] );
					is_done = true;
				}
			}

			if ( is_done ) {
				window.clearInterval( tmr_check );
				process_document();
			}
		}

		// Closes the popup
		function do_close_popup( close_it ) {
			if ( undefined !== close_it ) {
				me.data.close_popup = close_it;
			}

			if ( IncPopup.recent_ajax_calls ) {
				me.data.ajax_history = IncPopup.recent_ajax_calls;

				if ( IncPopup.recent_ajax_calls.length ) {
					me.data.last_ajax = IncPopup.recent_ajax_calls[0];
				}
			} else {
				me.data.ajax_history = [];
				me.data.last_ajax = {};
			}

			jQuery( document ).trigger( 'popup-submit-done', [me, me.data] );

			if ( me.data.close_popup ) {
				me.close_popup();
				return true;
			} else {
				return false;
			}
		}

		// Replaces the popup contents with some new contents.
		function do_replace_contents( contents, title, subtitle ) {
			var new_content = contents,
				el_inner = popup.find( '.wdpu-msg-inner' ),
				el_title = popup.find( '.wdpu-title' ),
				el_subtitle = popup.find( '.wdpu-subtitle' );

			if ( ! ( new_content instanceof jQuery ) ) {
				new_content = jQuery( '<div></div>' ).html( contents );
			}

			if ( new_content instanceof jQuery ) {
				if ( new_content.hasClass( 'wdpu-msg-inner' ) ) {
					el_inner.replaceWith( new_content );
				} else {
					el_inner.find( '.wdpu-content' )
						.empty()
						.append( new_content );
				}
			}

			if ( undefined !== title ) {
				el_title.html( title );
			}
			if ( undefined !== subtitle ) {
				el_subtitle.html( subtitle );
			}

			// Re-initialize the local DOM cache.
			jQuery( document ).trigger( 'popup-init', [me, me.data] );
		}

		// Executed once the iframe is fully loaded.
		function process_document() {
			var inner_new, inner_old, html, external, close_on_fail;

			// Allow other javascript functions to pre-process the event.
			jQuery( document ).trigger( 'popup-submit-process', [frame, me, me.data] );

			/*
			 * Use the event jQuery('document').on('popup-submit-process')
			 * to set `data.form_submit = false` to prevent form handling.
			 */
			if ( ! me.data.form_submit ) { return false; }

			if ( 'ignore' === me.data.form_submit ) {
				close_on_fail = false;
			} else {
				close_on_fail = true;
			}

			try {
				// grab the HTML from the body, using the raw DOM node (frame[0])
				// and more specifically, it's `contentDocument` property.
				html = jQuery( po_id, frame[0].contentDocument );
				external = me.data.did_ajax;
			} catch ( err ) {
				// In case the iframe link was an external website the above
				// line will most likely cause a security issue.
				html = jQuery( '<html></html>' );
				external = true;
			}

			me.data.close_popup = false;
			msg.removeClass( 'wdpu-loading' );

			// Get the new and old Popup Contents.
			inner_new = html.find( '.wdpu-msg-inner' );
			inner_old = popup.find( '.wdpu-msg-inner' );

			// remove the temporary iframe.
			jQuery( "#wdpu-frame" ).remove();
			me.data.last_ajax = undefined;

			if ( 'close' === me.data.form_submit ) {
				// =========================================================
				// Admin defined to close this popup after every form submit

				do_close_popup( true );

			} else if ( me.data.new_content ) {
				// =========================================================
				// Popup contents were explicitely defined by the javascript
				// event 'popup-submit-process'

				do_replace_contents(
					me.data.new_content,
					me.data.new_title,
					me.data.new_subtitle
				);

			} else if ( external ) {
				// =========================================================
				// For external pages we have no access to the response:
				// Close the popup!

				// E.g. Contact Form 7

				do_close_popup( close_on_fail );

			} else if ( ! html.length || ! html.html().length ) {
				// =========================================================
				// The PopUp is completely empty, possibly another
				// ajax-handler did process the form. Keep the popup open.

				// E.g. Gravity Forms

				do_close_popup( true );

			} else if ( ! inner_old.length || ! inner_new.length || ! inner_new.text().length ) {
				// =========================================================
				// A new page was loaded that does not contain new content
				// for the current PopUp. Close the popup!

				do_close_popup( close_on_fail );

			} else {
				// =========================================================
				// Update the Popup contents.

				do_replace_contents( inner_new );

			}
		}
		// end of process_document()

		if ( 'redirect' !== me.data.form_submit ) {
			// Only change the form target when NOT redirecting
			frame = jQuery( '<iframe id="wdpu-frame" name="wdpu-frame"></iframe>' )
				.hide()
				.appendTo( 'body' );

			// Set form target to the hidden frame.
			form.attr( 'target', 'wdpu-frame' );
			inp_popup.appendTo( form ).val( 'raw' );
		}

		msg.addClass( 'wdpu-loading' );

		if ( 'redirect' === me.data.form_submit ) {
			/**
			 * When redirecting always close the popup
			 */
			window.setTimeout(function() {
				me.close_popup();
			}, 10);
		} else {
			if ( IncPopup.doing_ajax ) {
				// E.g. Contact Form 7
				me.data.did_ajax = true;
				iteration = 0;
				tmr_check = window.setInterval( check_state, 50 );
			} else {
				// E.g. Gravity Forms
				me.data.did_ajax = false;
				frame.load( process_document );
			}
		}

		return true;
	};


	/*-----  Init  ------*/


	me.init = function init() {
		if ( ! _options.popup ) {
			me.load_popup();
		} else {
			me.have_popup = true;
			me.data = _options.popup;
			me.exec_scripts();
			me.prepare();
		}
	};

	/**
	 * If the popup provides custom javascript code we execute it here.
	 * Custom script might be provided by rules that are executed in JS such
	 * as the window-width rule or on-click behavior.
	 */
	me.exec_scripts = function exec_scripts() {
		var fn;
		if ( undefined !== me.data.script ) {
			fn = new Function( 'me', me.data.script );
			fn( me );
		}
	};


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/

	// Alias for close_popup
	me.close = function close() {
		return me.close_popup();
	};

	// Alias for show_popup
	me.open = function open() {
		return me.show_popup();
	};

	// Alias for popup_status
	me.status = function status() {
		return {
			'state': IncPopup.po_status,
			'queue': IncPopup.po_queue,
			'current': IncPopup.po_current,
		};
	};

	// Get a cookie value.
	me.get_cookie = function get_cookie( name ) {
		var i, c, cookie_name, value,
			ca = document.cookie.split( ';' );

		if ( me.data && me.data.popup_id ) {
			cookie_name = name + '-' + me.data.popup_id + "=";
		} else {
			cookie_name = name + "=";
		}

		for ( i = 0; i < ca.length; i += 1 ) {
			c = ca[i];
			while ( c.charAt(0) === ' ' ) {
				c = c.substring( 1, c.length );
			}
			if (c.indexOf( cookie_name ) === 0 ) {
				return c.substring( cookie_name.length, c.length );
			}
		}
		return null;
	};

	// Saves the value into a cookie.
	me.set_cookie = function set_cookie( name, value, days ) {
		var date, expires, cookie_name;

		if ( _options.preview ) { return; }

		if ( ! isNaN( days ) ) {
			date = new Date();
			date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
			expires = "; expires=" + date.toGMTString();
		} else {
			expires = "";
		}

		if ( me.data && me.data.popup_id ) {
			cookie_name = name + '-' + me.data.popup_id;
		} else {
			cookie_name = name;
		}

		document.cookie = cookie_name + "=" + value + expires + "; path=/";
	};

	/*-----  Finished  ------*/

	// Only expose required functions of the PopUp.
	var expose = {};
	expose.extend = me;

	if ( me.data && me.data.preview ) {
		// Preview: The popup is displayed by popup-admin.js
		expose.init = me.init;
		expose.load = me.load_popup;
	} else if ( _options.dynamic ) {
		// Experimental feature to dynamically spawn popups via code.
		// Handles the WP action hook `wdev-popup`.
		me.init();

		expose.open = me.show_popup;
		expose.close = me.close_popup;
		expose.status = me.status;
	} else {
		// Default behavior: Simply return the exposed interface.
		expose.init = me.init;
		expose.load = me.load_popup;
	}

	return expose;
};
})();
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
