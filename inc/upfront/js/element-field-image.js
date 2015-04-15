(function _define_field_image() {
define(
[
	'text!' + _popup_uf_data.base_url + 'tpl/templates.html' + _popup_uf_data.cache_ver
],
/**
 * Defines the custom input field: Image Field
 *
 * @since  4.8.0.0
 */
function _load_field_image( tpl_source ) {

	/**
	 * Define the translations.
	 *
	 * NOTE: This uses the .fields sub-collection of the l10n data!!
	 */
	var l10n = Upfront.Settings.l10n.popup_element.fields;

	/**
	 * HTML markup for custom settings fields.
	 */
	var templates = jQuery( tpl_source ),
		tpl_image_field = templates.filter( '#wdpu-image-field' );

	/**
	 * Custom Field Definition: ImageField
	 * This field can be used to select a single image.
	 */
	var ImageField = Upfront.Views.Editor.Field.Field.extend({

		// ===== Field HTML template.
		template: _.template(
			tpl_image_field.html()
		),

		// ===== Field event handlers.
		events: {
			'click .select-image' : 'on_select',
			'click .remove-image' : 'on_remove'
		},

		// ========== ImageField --- Render
		render: function render() {
			var code, data, me = this;

			data = {
				field_name: me.get_field_name(),
				image: me.get_saved_value(),
				l10n: l10n
			};

			code = me.template( data );
			me.$el.html( code );

			// Set correct visiility for the "remove image" button.
			if ( data.image && data.image.length ) {
				me.$el.find( '.remove-image' ).show();
			} else {
				me.$el.find( '.remove-image' ).hide();
			}
		},

		// ========== ImageField --- On_select
		on_select: function on_select( ev ) {
			ev.preventDefault();
			this.select_image();
		},

		// ========== ImageField --- On_remove
		on_remove: function on_remove( ev ) {
			ev.preventDefault();
			this.remove_image();
		},

		// ========== ImageField --- Select_image
		select_image: function select_image( ev ) {
			var me = this,
				selectorOptions = {
					multiple: false,
					preparingText: l10n.preparing_image,
					element_id: this.model.get_property_value_by_name( 'element_id' ),
					customImageSize: {
						width: 200,
						height: 200
					}
				};

			if ( ev ) {
				ev.preventDefault();
			}

			function set_image( result ) {
				var url, image = false;

				for ( var i in result ) {
					if ( result.hasOwnProperty( i ) && typeof( i ) !== 'function' ) {
						image = result[i];
						break;
					}
				}

				if ( image ) {
					url = image.full[0];

					// Set the new property value.
					me.set_value( url );
					me.trigger( 'changed', url );

					// Display the preview in the settings panel.
					me.$el.find( '.wdpu-image-field-preview img' )
						.attr( 'src', url )
						.show();

					// Show the "remove image" button.
					me.$el.find( '.remove-image' ).show();
				}

				Upfront.Views.Editor.ImageSelector.close();
			};

			Upfront.Views.Editor.ImageSelector
				.open( selectorOptions )
				.done( set_image );
		},

		// ========== ImageField --- Remove_image
		remove_image: function remove_image( ev ) {
			var me = this;

			if ( ev ) {
				ev.preventDefault();
			}

			// Reset the new property value.
			me.set_value( '' );
			me.trigger( 'changed', '' );

			// Remove the preview in the settings panel.
			me.$el.find( '.wdpu-image-field-preview img' )
				.attr( 'src', '' )
				.hide();

			// Hide the "remove image" button.
			me.$el.find( '.remove-image' ).hide();

		}

	});

	// Return the module object.
	return ImageField;

});
})();