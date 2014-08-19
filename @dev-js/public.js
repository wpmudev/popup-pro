;(function () {
	var Popup = function( _options ) {

		var me = this,
			$doc = jQuery( document ),
			$win = jQuery( window ),
			$po_div = null,
			$po_msg = null,
			$po_close = null,
			$po_hide = null,
			$po_move = null,
			$po_resize = null,
			$po_img = null,
			$po_back = null
			;

		this.data = {};
		this.have_popup = false;
		this.ajax_data = {};

		/**
		 * Close PopUp and set the "never see again" flag.
		 */
		this.close_forever = function close_forever() {
			var expiry = me.data.expiry || 365;

			me.close_popup();
			if ( _options['preview'] ) { return false; }

			me.set_cookie( 'po_h', 1, expiry );
			return false;
		};

		/**
		 * Close PopUp.
		 * Depending on the "multi_open" flag it can be opened again.
		 */
		this.close_popup = function close_popup() {
			jQuery( 'html' ).removeClass( 'has-popup' );

			if ( me.data.display_data['click_multi'] ) {
				$po_back.hide();
				$po_div.hide();
			} else {
				$po_back.remove();
				$po_div.remove();

				me.have_popup = false;
			}

			$doc.trigger( 'popup-closed' );
			// Legacy trigger.
			$doc.trigger( 'popover-closed' );

			if ( ! me.have_popup ) {
				me.next_popup();
			}
			return false;
		};

		/**
		 * When user clicked on the background-layer.
		 */
		this.background_clicked = function background_clicked( ev ) {
			var el = jQuery( ev.target );

			if ( el.hasClass( 'wdpu-background' ) ) {
				if ( ! me.data.overlay_close ) { return; }

				me.close_popup();
			}
		}

		/**
		 * Resize and move the PopUp. Triggered when PopUp is loaded and
		 * window is resized.
		 */
		this.move_popup = function move_popup() {
			var new_width, new_height, reduce_el, reduce_w_by = 0, reduce_h_by = 0;

			// Resize, if custom-size is active.
			if ( me.data.custom_size ) {
				if ( me.data.height && ! isNaN( me.data.height ) ) {
					if ( $po_resize.data( 'reduce-height' ) ) {
						reduce_el = jQuery( $po_resize.data( 'reduce-height' ) );
						reduce_h_by = reduce_el.outerHeight();
					}
					new_height = me.data.height - reduce_h_by;
					if ( new_height < 100 ) { new_height = 100; }
					$po_resize.height( new_height );
				}

				if ( me.data.width && ! isNaN( me.data.width ) ) {
					if ( $po_resize.data( 'reduce-width' ) ) {
						reduce_el = jQuery( $po_resize.data( 'reduce-width' ) );
						reduce_w_by = reduce_el.outerWidth();
					}
					new_width = me.data.width - reduce_w_by;
					if ( new_width < 100 ) { new_width = 100; }
					$po_resize.width( new_width );
				}
			}

			// Short delay before positioning the popup to give the browser time
			// to show/resize the popup (20ms ~ 1 screen refresh)
			window.setTimeout(function() {
				if ( ! $po_move.hasClass( 'no-move' ) ) {
					var win_width = $win.width(),
						win_height = $win.height(),
						msg_width = $po_msg.outerWidth(),
						msg_height = $po_msg.outerHeight(),
						msg_left = (win_width - msg_width) / 2,
						msg_top = (win_height - msg_height) / 2;

					// Move window horizontally.
					if ( msg_width+100 > win_width || msg_left < 0 ) {
						if ( isNaN( me.data._switch_width ) ) {
							me.data._switch_width = 800;
							$po_move.addClass('small-width').css({ 'left': '' });
						}
					} else if ( me.data._switch_width < msg_width ) {
						me.data._switch_width = undefined;
						$po_move.removeClass('small-width');
						$po_move.css({ 'left': msg_left });
					} else {
						$po_move.css({ 'left': msg_left });
					}

					// Move window vertically.
					if ( msg_top < 10 ) { msg_top = 10; }
					$po_move.css({ 'top': msg_top });
				}

				// Move the image.
				if ( $po_img.length ) {
					var offset_x, offset_y,
						img_width = $po_img.width(),
						img_height = $po_img.height(),
						box_width = $po_img.parent().width(),
						box_height = $po_img.parent().height();

					// Center horizontally.
					if ( img_width > box_width ) {
						// Center image.
						offset_x = (box_width - img_width) / 2;
						$po_img.css({ 'margin-left': offset_x });
					} else {
						// Align image according to layout.
						$po_img.css({ 'margin-left': 0 });
					}

					// Center vertially.
					if ( img_height > box_height ) {
						// Center image.
						offset_y = (box_height - img_height) / 2;
						$po_img.css({ 'margin-top': offset_y });
					} else {
						// Align image according to layout.
						$po_img.css({ 'margin-top': 0 });
					}
				}
			}, 20);
		};

		/**
		 * Reject the current PopUp: Do not display it.
		 */
		this.reject = function reject() {
			me.have_popup = false;
			me.data = {};
		};

		/**
		 * Check if the PopUp is ready to be displayed.
		 * If it is ready then it is displayed.
		 */
		this.maybe_show_popup = function maybe_show_popup() {
			me.fetch_dom();
			// Move the PopUp out of the viewport but make it visible.
			// This way the browser will start to render the contents and there
			// will be no delay when the PopUp is made visible later.
			$po_div.css({
				'opacity': 0,
				'z-index': -1,
				'position': 'absolute',
				'left': -1000,
				'width': 100,
				'right': 'auto',
				'top': -1000,
				'height': 100,
				'bottom': 'auto'
			}).show();

			$doc.trigger( 'popup-init', [me, me.data] );

			if ( me.have_popup ) {
				switch ( me.data.display ) {
					case 'scroll':
						$win.on( 'scroll', me.show_at_position );
						break;

					case 'anchor':
						$win.on( 'scroll', me.show_at_element );
						break;

					case 'delay':
						var delay = me.data.display_data.delay * 1000;
						if ( 'm' == me.data.display_data.delay_type ) {
							delay *= 60;
						}

						window.setTimeout( function() {
							me.show();
						}, delay );
						break;

					default:
						// A custom action will show the PopUp (e.g. click/leave)
						setTimeout(function() {
							if ( 'function' == typeof me.custom_handler ) {
								me.custom_handler( me );
							}
						}, 20);
				}

			} else {
				// PopUp was rejected during popup-init event. Do not display.
				me.next_popup();
			}
		};

		/**
		 * Observe the scroll-top to trigger the PopUp.
		 */
		this.show_at_position = function show_at_position( ev ) {
			var height, perc,
				el = jQuery( this ),
				top = el.scrollTop();

			switch ( me.data.display_data.scroll_type ) {
				case 'px':
					if ( top >= me.data.display_data.scroll ) {
						$win.off( 'scroll', me.show_at_position );
						me.show();
					}
					break;

				case '%':
				default:
					height = $doc.height() - $win.height();
					perc = 100 * top / height;

					if ( perc >= me.data.display_data.scroll ) {
						$win.off( 'scroll', me.show_at_position );
						me.show();
					}
					break;
			}
		};

		/**
		 * Tests if a specific HTML element is visible to trigger the PopUp.
		 * We intentionally calculate el_top every time this function is called
		 * because the element may be hidden or not present at page load.
		 */
		this.show_at_element = function show_at_element( ev ) {
			var anchor = jQuery( me.data.display_data.anchor ),
				view_top = $win.scrollTop(),
				view_bottom = view_top + $win.height(),
				el_top = anchor.offset().top,
				offset = view_bottom - el_top;

			// When 10px of the element are visible show the PopUp.
			if ( offset > 10 ) {
				$win.off( 'scroll', me.show_at_element );
				me.show();
			}
		};

		/**
		 * Display the PopUp!
		 */
		this.show = function show() {
			$po_back.on( 'click', me.background_clicked );
			$doc.on( 'popup-closed', me.reinit );

			$win.off("resize.popup").on("resize.popup", function () {
				me.move_popup(me.data);
			});

			$po_div.show().removeAttr( 'style' );
			$po_back.show();

			me.move_popup(me.data);

			jQuery( 'html' ).addClass( 'has-popup' );

			$po_hide.off( "click", me.close_forever )
				.on( "click", me.close_forever );

			if ( me.data && me.data.close_hide ) {
				$po_close.off( 'click', me.close_forever )
					.on( 'click', me.close_forever );
			} else {
				$po_close.off( 'click', me.close_popup )
					.on( 'click', me.close_popup );
			}

			$po_msg.hover(function() {
				jQuery( '.claimbutton' ).removeClass( 'hide' );
			}, function() {
				jQuery( '.claimbutton' ).addClass( 'hide' );
			});

			$doc.trigger( 'popup-displayed', [me.data, me] );
			// Legacy trigger.
			$doc.trigger( 'popover-displayed', [me.data, me] );
		};


		/*-----  Dynamically load PopUps  ------*/


		/**
		 * Finds the PopUp DOM elements and stores them in protected member
		 * variables for easy access.
		 */
		this.fetch_dom = function fetch_dom() {
			// The top container of the PopUp.
			$po_div = jQuery( '#' + me.data['html_id'] );

			// The container that should be resized (custom size).
			$po_resize = $po_div.find( '.resize' );

			// The container that should be moved (centered on screen).
			$po_move = $po_div.find( '.move' );

			// The container that holds the message:
			// For new styles this is same as $po_resize.
			// For old popup styles this is a different contianer...
			$po_msg = $po_div.find( '.wdpu-msg' );

			// Close button.
			$po_close = $po_div.find( '.wdpu-close' );

			// Hide forever button.
			$po_hide = $po_div.find( '.wdpu-hide-forever' );

			// Featured image.
			$po_img = $po_div.find( '.wdpu-image > img' );

			// The modal background.
			if ( $po_div.hasClass( 'wdpu-background' ) ) {
				$po_back = $po_div;
			} else {
				$po_back = $po_div.find( '.wdpu-background' );

				if ( ! $po_back.length ) {
					$po_back = jQuery( '.wdpu-background' );
				}
			}

			if ( ! $po_move.length ) { $po_move = $po_div; }
			if ( ! $po_resize.length ) { $po_resize = $po_div; }
		};

		/**
		 * Insert the PopUp CSS and HTML as hidden elements into the DOM.
		 */
		this.prepare_dom = function prepare_dom() {
			if ( me.data['html'] === '' ) { return false; }

			jQuery( '<style type="text/css">' + me.data['styles'] + '</style>' )
				.appendTo('head');

			jQuery( me.data['html'] )
				.appendTo('body');

			me.fetch_dom();

			$po_div.hide();
			$po_back.hide();

			me.maybe_show_popup();
		};

		/**
		 * Load popup data via ajax.
		 */
		this.load_popup = function load_popup( id, data ) {
			var ajax_args, ajax_data,
				po_id = 0,
				thefrom = window.location,
				thereferrer = document.referrer;

			me.have_popup = false;

			var handle_done = function handle_done( data ) {
				me.data = data;

				if ( data ) {
					me.have_popup = true;
					me.exec_scripts();
					me.prepare_dom();
				}
			};

			// Legacy: force_popover = load a popup_id by ID.
			if ( typeof force_popover != 'undefined' ) {
				po_id = force_popover.toString();
			}

			// New way of specifying popup ID is via param: load(id)
			if ( typeof id != 'undefined' ) {
				po_id = id.toString();
			}

			_options['ajax_data'] = _options['ajax_data'] || {};
			ajax_data = jQuery.extend( {}, _options['ajax_data'] );

			ajax_data['action']      = 'inc_popup',
			ajax_data['do']          = _options['do'],
			ajax_data['thefrom']     = thefrom.toString(),
			ajax_data['thereferrer'] = thereferrer.toString()

			if ( po_id ) { ajax_data['po_id'] = po_id; }
			if ( data ) { ajax_data['data'] = data; }
			if ( _options['preview'] ) { ajax_data['preview'] = true; }

			ajax_args = {
				url:           _options['ajaxurl'],
				dataType:      'jsonp',
				jsonpCallback: 'po_data',
				data:          ajax_data,
				success: function( data ) {
					handle_done( data );
				},
				complete: function() {
					$doc.trigger( 'popup-load-done', [me.data, me] );
				}
			};
			return jQuery.ajax(ajax_args);
		};

		/**
		 * Try to load the next PopUp from the server.
		 */
		this.next_popup = function next_popup() {
			console.log ('try to fetch next popup...');
		};


		/*-----  Init  ------*/


		this.init = function init() {
			if ( ! _options['popup'] ) {
				me.have_popup = false;
				me.load_popup();
			} else {
				me.have_popup = true;
				me.data = _options['popup'];
				me.exec_scripts();
				me.maybe_show_popup();
			}
		};

		/**
		 * Used for certain rules (e.g. on-click rule) to show the PopUp
		 * again when the rule validates a second time.
		 */
		this.reinit = function reinit() {
			if ( me.data.display == 'click' && me.data.display_data['click_multi'] ) {
				me.maybe_show_popup();
			}
		};

		/**
		 * If the popup provides custom javascript code we execute it here.
		 * Custom script might be provided by rules that are executed in JS such
		 * as the window-width rule or on-click behavior.
		 */
		this.exec_scripts = function exec_scripts() {
			if ( undefined !== me.data.script ) {
				var fn = new Function( 'me', me.data.script );
				fn( me );
			}
		}


		/*======================================*\
		==========================================
		==                                      ==
		==           HELPER FUNCTIONS           ==
		==                                      ==
		==========================================
		\*======================================*/


		// Get a cookie value.
		this.get_cookie = function get_cookie( name ) {
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
		this.set_cookie = function set_cookie( name, value, days ) {
			var date, expires, cookie_name;

			if ( _options['preview'] ) { return; }

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

		// Only expose the "init" and "load" functions of the PopUp.
		return {
			init: me.init,
			load: me.load_popup,
			extend: me
		};
	};


	// Initialize the PopUp one the page is loaded.
	jQuery(function() {
		window.inc_popup = new Popup( _popup_data );
		if ( _popup_data['noinit'] || _popup_data['preview'] ) { return; }
		inc_popup.init();
	});

})();