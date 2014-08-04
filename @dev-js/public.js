;(function () {
	var Popup = function( _options ) {

		var me = this,
			$doc = jQuery( document ),
			$win = jQuery( window ),
			$po_bg = jQuery( '#darkbackground' ),
			$po_div = null,
			$po_msg = null,
			$po_close = null,
			$po_hide = null
			;

		this.data = {};

		/**
		 * Close Pop Up and set the "never see again" flag.
		 */
		this.close_forever = function close_forever() {
			var expiry = me.data.expiry || 365;

			me.close_popup();
			if ( _options['preview'] ) { return false; }

			me.set_cookie( 'po_h-', 1, expiry );
			return false;
		};

		/**
		 * Close Pop Up.
		 * Depending on the "multi_open" flag it can be opened again.
		 */
		this.close_popup = function close_popup() {
			if ( me.data.multi_open ) {
				$po_bg.hide();
				$po_div.hide();
			} else {
				$po_bg.remove();
				$po_div.remove();
			}

			$doc.trigger( 'popup-closed' );
			// Legacy trigger.
			$doc.trigger( 'popover-closed' );
			return false;
		};

		this.move_popup = function move_popup() {
			if ( me.data.custom_size ) {
				$po_div.width(me.data.width)
					.height(me.data.height);
			}

			if ( ! $po_div.is( ":visible" ) ) {
				$po_div.css({
					'top': $win.height()
				});
				$po_div.show();
			}

			// Short delay before positioning the popup to give the browser time
			// to show/resize the popup (20ms ~ 1 screen refresh)
			window.setTimeout(function() {
				$po_div.css({
					'top':  ($win.height() - $po_msg.height()) / 2,
					'left': ($win.width()  - $po_msg.width()) / 2
				});
			}, 20);
		};


		/**
		 * Check if the Pop Up is ready to be displayed.
		 * If it is ready then it is displayed.
		 */
		this.maybe_show_popup = function maybe_show_popup() {
			me.fetch_dom();

			$doc.trigger( 'popup-init', [undefined, me.data] );
			// Legacy trigger.
			$doc.trigger( 'popover-init', [undefined, me.data] );

			setTimeout(function () {
				$po_div.hide();

				// We're waiting for some javascript event before showing the popup.
				if ( me.data.wait_for_event ) { return false; }

				window.setTimeout(function() {
					me.show();
					if ( me.data.multi_open ) {
						$doc.on('popup-closed', me.reinit);
					}
				}, me.data.delay);
			}, 500);
		}

		/**
		 * Display the popup!
		 */
		this.show = function show() {
			me.move_popup(me.data);

			$win.off("resize.popup").on("resize.popup", function () {
				me.move_popup(me.data);
			});

			$po_div.show();
			$po_bg.show();

			$po_hide.off( "click", me.close_forever )
				.on( "click", me.close_forever );

			if ( me.data && me.data.close_hide ) {
				$po_close.off( "click", me.close_forever )
					.on( "click", me.close_forever );
			} else {
				$po_close.off( "click", me.close_popup )
					.on( "click", me.close_popup );
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


		/*-----  Dynamically load Pop Ups  ------*/


		/**
		 * Finds the Pop Up DOM elements and stores them in protected member
		 * variables for easy access.
		 */
		this.fetch_dom = function fetch_dom() {
			$po_div = jQuery( '#' + me.data['html_id'] );
			$po_msg = $po_div.find( '#message' );
			$po_close = $po_div.find( '#closebox' );
			$po_hide = $po_div.find( '#clearforever' );
		};

		/**
		 * Insert the Pop Up CSS and HTML as hidden elements into the DOM.
		 */
		this.prepare_dom = function prepare_dom() {
			if ( me.data['html'] === '' ) { return false; }

			jQuery( '<style type="text/css">' + me.data['styles'] + '</style>' )
				.appendTo('head');

			jQuery( me.data['html'] )
				.appendTo('body');

			me.fetch_dom();

			$po_div.hide();
			$po_bg.hide();

			me.maybe_show_popup();
		};

		/**
		 * Load popup data via ajax.
		 */
		this.load_popup = function load_popup( id, data ) {
			var po_id = 0,
				thefrom = window.location,
				thereferrer = document.referrer;

			var handle_done = function handle_done( data ) {
				me.data = data;

				if ( data ) {
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

			return jQuery.ajax({
				url:           _options['ajaxurl'],
				dataType:      'jsonp',
				jsonpCallback: 'po_data',
				data: {
					'action':    'inc_popup',
					'do':        _options['do'],
					thefrom:     thefrom.toString(),
					thereferrer: thereferrer.toString(),
					po_id:       po_id,
					data:        data || {}
				},
				success: function( data ) {
					handle_done( data );
				},
				complete: function() {
					$doc.trigger( 'popup-load-done', [me.data, me] );
				}
			});
			return false;
		};


		/*-----  Init  ------*/


		this.init = function init() {
			if ( ! _options['popup'] ) {
				me.load_popup();
			} else {
				me.data = _options['popup'];
				me.maybe_show_popup();
			}
		};

		/**
		 * Used for certain rules (e.g. on-click rule) to show the Pop Up
		 * again when the rule validates a second time
		 */
		this.reinit = function reinit() {
			me.maybe_show_popup();
		};


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


		// Only expose the "init" and "load" functions of the Pop Up.
		return {
			init: this.init,
			load: this.load_popup
		};
	};


	// Initialize the Pop Up one the page is loaded.
	jQuery(function() {
		window.inc_popup = new Popup( _popup_data );
		if ( _popup_data['noinit'] || _popup_data['preview'] ) { return; }
		inc_popup.init();
	});

})();