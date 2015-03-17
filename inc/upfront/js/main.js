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
		if ( undefined === Upfront.Events || undefined === Upfront.Events.on ) {
			loading_attempts += 1;
			window.setTimeout( init_module, 20 );
			return;
		}

		/*
		 * Upfront.Events is actually the Backbone.Events model.
		 * At some points Upfront will broadcast events. To hook into these
		 * events we need to register our event handler using Upfront.Events.on
		 */
		Upfront.Events.on( 'Upfront:loaded', function() {
			/*
			 * Dependencies:
			 * - A normal URL will be loaded and interpreted as javascript
			 * - URL starting with 'text!' will be loaded and passed as param to
			 *   the callback function.
			 */
			var dependencies = [
				Upfront.popup_config.base_url + 'js/upfront-element.js',
				'text!' + Upfront.popup_config.base_url + 'css/upfront-element.css'
			];

			require(
				dependencies,
				function( script, styles ) {
					styles = styles.replace(
						'[BASE_URL]',
						Upfront.popup_config.base_url
					);
					jQuery( 'head' ).append( '<style>' + styles + '</style>' );
					Upfront.Util.log( '[Plugin PopUp] loaded' );
				}
			);
		});
	}

	// Try to load and setup the plugin for Upfront.
	init_module();

});
