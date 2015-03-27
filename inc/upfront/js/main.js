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
	}

	// Try to load and setup the plugin for Upfront.
	init_module();

	// Remove the empty popup preview containers from the page.
	var el_previews = jQuery( '.upfront-popup_element_object' ),
		preview_rows = el_previews.closest( '.upfront-output-wrapper' );
	preview_rows.remove();

});
