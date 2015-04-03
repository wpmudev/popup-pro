(function _define_settings_itemgroup() {
define(
[
	'text!' + _popup_uf_data.base_url + 'tpl/image_field.html'
],
/**
 * Defines the settings item-group base class
 *
 * @since  4.8.0.0
 */
function _load_settings_itemgroup( tpl_image_field ) {
	/**
	 * Define the translations.
	 *
	 * NOTE: This uses the .settings sub-collection of the l10n data!!
	 */
	var l10n = Upfront.Settings.l10n.popup_element;

	/**
	 * Custom Field Definition: ImageField
	 * This field can be used to select a single image.
	 */
	var ImageField = Upfront.Views.Editor.Field.Field.extend({

		// ===== Field HTML template.
		template: _.template(
			jQuery( tpl_image_field ).html()
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
					preparingText: l10n.preparing_img,
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

	/**
	 * Simply a collection of fields.
	 * This is the base-class of setting fields.
	 *
	 * We also add custom field-types here that can be used by child classes via
	 * the `this` scope. E.g.
	 * var img_field = new this.Fields.ImageField( {...} );
	 */
	var PopupSettings_ItemGroup = Upfront.Views.Editor.Settings.Item.extend({

		// ===== ImageField object
		Fields: {
			ImageField: ImageField
		},

		// ========== PopupSettings_ItemGroup --- Render
		render: function render() {
			Upfront.Views.Editor.Settings.Item.prototype.render.call( this );
			this.$el.find('.upfront-settings-item-content').addClass( 'clearfix' );
		},

		// ========== PopupSettings_ItemGroup --- Register_change
		register_change: function register_change() {
			this.fields.each(function loop_fields( field ) {
				window.console.log ( 'register change:', field, field.get_value())
				field.property.set(
					{'value': field.get_value()},
					{'silent': false}
				);
			});
			this.trigger( 'popup:settings:changed' );
		}

	});

	// Return the module object.
	return PopupSettings_ItemGroup;

});
})();