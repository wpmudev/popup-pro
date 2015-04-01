jQuery(function() {
	var loading_attempts = 0;

	function init_module() {
		/*
		 * When Upfront is not ready after 100 iterations then give up...
		 */
		if ( loading_attempts > 100 ) {
			return;
		}

		/*
		 * If the Upfront framework was not fully initialized then try again
		 * after a short delay. This gives Upfront time to finish setup.
		 */
		if ( undefined === window.Upfront || undefined === Upfront.Events || undefined === Upfront.Events.on ) {
			loading_attempts += 1;
			window.setTimeout( init_module, 20 );
			return;
		}

		/**
		 * Upfront.Events is actually the Backbone.Events model.
		 * At some points Upfront will broadcast events. To hook into these
		 * events we need to register our event handler using Upfront.Events.on
		 */

		/**
		 * == application:loaded:layout_editor
		 * The first event that is broadcast when the user changes into
		 * edit-mode.
		 */
		Upfront.Events.on( 'application:loaded:layout_editor', function() {

			/*
			 * Dependencies:
			 * - A normal URL will be loaded and interpreted as javascript
			 * - URL starting with 'text!' will be loaded and passed as param to
			 *   the callback function.
			 */
			var dependencies = [
				_popup_uf_data.base_url + 'js/element.js',
				'text!' + _popup_uf_data.base_url + 'css/element.css?v5'
			];

			require(
				dependencies,
				function( script, styles ) {
					// Replace placeholders inside the CSS content.
					styles = styles.replace(
						'[BASE_URL]',
						_popup_uf_data.base_url
					);

					jQuery( 'head' ).append( '<style>' + styles + '</style>' );
				}
			);
		});

		/**
		 * == settings:prepare
		 * This event indicates that the CSS-Editor is about to load.
		 * We should register our Element now, else it will be displayed
		 * as "Unknown Element"
		 */
		Upfront.Events.on( 'settings:prepare', function() {
			var css_element = {label: _popup_uf_data.label, id: _popup_uf_data.type};
			Upfront.Application.cssEditor.elementTypes['PopupModel'] = css_element;
		});


		// DEBUGGING EXTENSION
		// =====================================================================
		//
		//
		Upfront.Debug = Upfront.Debug || {};
		Upfront.Debug = _.extend(Upfront.Debug, {
			debugging_events: false,
			setup_timer: false,
			setup_timer_iterations: 0,
			event_list: false,
			event_counter: 1000000,

			/**
			 * Turn Event-Logging on/off.
			 *
			 * @api
			 *
			 * @param bool state True means that all events will be logged.
			 * @param string event_list Comma-separated list of events to log.
			 *               If undefined/not a string then all events are logged.
			 */
			show_events: function show_events( state, event_list ) {
				if ( state == this.debugging_events ) { return; }
				this.debugging_events = (state == true);

				if ( typeof event_list == 'string' || event_list instanceof String ) {
					Upfront.Debug.set_cookie( 'uf_debug_eventlist', event_list );
				} else {
					Upfront.Debug.del_cookie( 'uf_debug_eventlist' );
				}

				if ( this.debugging_events ) {
					Upfront.Debug.set_cookie( 'uf_debug_events', true );
				} else {
					Upfront.Debug.del_cookie( 'uf_debug_events' );
				}

				Upfront.Debug.setup_debugger();
			},

			/**
			 * Display a stacktrace in the console window.
			 *
			 * @api
			 */
			trace: function trace() {
				var err = new Error();
				window.console.log( '[UF LOG] Stacktrace: ', err.stack );
			},

			/**
			 * Set a cookie.
			 * @internal
			 */
			set_cookie: function set_cookie( name, value, exdays ) {
				var d = new Date();
				if ( isNaN( exdays ) ) { exdays = 31; }
				d.setTime( d.getTime() + (exdays*24*60*60*1000) );
				var expires = "expires=" + d.toUTCString();
				document.cookie = name + "=" + value + "; " + expires;
			},

			/**
			 * Remove a cookie.
			 * @internal
			 */
			del_cookie: function del_cookie( name ) {
				Upfront.Debug.set_cookie( name, false, -1 );
			},

			/**
			 * Return a cookie value.
			 * @internal
			 */
			get_cookie: function get_cookie( name ) {
				var name = name + "=";
				var ca = document.cookie.split( ';' );
				for ( var i=0; i<ca.length; i++ ) {
					var c = ca[i];
					while (c.charAt(0)==' ') c = c.substring(1);
					if ( c.indexOf(name) == 0 ) {
						return c.substring(name.length, c.length);
					}
				}
				return "";
			},

			/**
			 * Initialize or update the debugging functions.
			 * @internal
			 */
			setup_debugger: function setup_debugger() {
				// Timer is used to keep the debugging upright during page load.
				// Upfront seems to overwrite the Upfront.Events object at least once during init...

				function start_timer() {
					Upfront.Debug.setup_timer = window.setInterval( Upfront.Debug.setup_debugger, 5 );
				};

				function stop_timer() {
					window.clearInterval( Upfront.Debug.setup_timer );
					Upfront.Debug.setup_timer = false;
				}

				var do_debug = false;
				if ( Upfront.Debug.get_cookie( 'uf_debug_events' ) ) { do_debug = true; }

				if ( do_debug ) {
					// Enforce debugging settings during the first 5 seconds of page load.
					if ( ! Upfront.Debug.setup_timer ) {
						start_timer();
					} else {
						// Note that browsers have minimum timer-interval of 4ms.
						// Also the interval can be longer when the browser is busy.
						// This is a very inaccurate measurement. But a simple one.
						Upfront.Debug.setup_timer_iterations += 1;
						if ( Upfront.Debug.setup_timer_iterations > 1000 ) {
							stop_timer();
						}
					}
				}

				if ( Upfront.Debug.get_cookie( 'uf_debug_events' ) ) {
					var event_list = Upfront.Debug.get_cookie( 'uf_debug_eventlist' );
					if ( event_list && event_list.length ) {
						if ( event_list != Upfront.Debug.event_list.toString() ) {
							event_list = event_list.split(',');
							Upfront.Debug.event_list = event_list;
							window.console.info( '[UF INFO] Starting to log following Upfront Events: ', Upfront.Debug.event_list );
						}
					} else {
						if ( Upfront.Debug.event_list ) {
							Upfront.Debug.event_list = false;
							window.console.info( '[UF INFO] Starting to log all Upfront Events.' );
						}
					}

					if ( undefined === Upfront.Events.trigger_orig ||
						Upfront.Events.trigger_orig === Upfront.Events.trigger
					) {
						var func = Upfront.Events.trigger;
						Upfront.Events.trigger_orig = func;
						Upfront.Events.trigger = function trigger( event ) {
							if ( ! Upfront.Debug.event_list ||
								Upfront.Debug.event_list.indexOf( event ) >= 0
							) {
								var params = Array.prototype.slice.call( arguments, 1 );
								var count = Upfront.Debug.event_counter.toString().slice( 1 );
								window.console.debug( '[UF EVENT ' + count + ']   <' + event + '>', params );
								Upfront.Debug.event_counter += 1;
							}
							return func.apply( this, arguments );
						}
					}
				} else {
					if ( undefined !== Upfront.Events.trigger_orig ) {
						Upfront.Events.trigger = Upfront.Events.trigger_orig;
						delete Upfront.Events.trigger_orig;
						window.console.info( '[UF INFO] Events will not be logged anymore.' );
					}
				}
			}

		});

		Upfront.Debug.setup_debugger();
		//
		//
		// =====================================================================
		//
	}

	// Try to load and setup the plugin for Upfront.
	init_module();

	// Remove the empty popup preview containers from the page.
	var el_previews = jQuery( '.upfront-popup_element_object' ),
		preview_rows = el_previews.closest( '.upfront-output-wrapper' );
	preview_rows.remove();

});
