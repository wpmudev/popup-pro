(function () {
define(
[
	// Loads no other modules
],
/**
 * Defines the settings item-group base class
 *
 * @since  4.8.0.0
 */
function() {

	/**
	 * Simply a collection of fields.
	 * This is the base-class of setting fields.
	 */
	var PopupSettings_ItemGroup = Upfront.Views.Editor.Settings.Item.extend({

		// ========== PopupSettings_ItemGroup --- Save_fields
		save_fields: function() {
			var model = this.model;

			function save_field( field ) {
				var data = field.get_value();
				if ( ! _.isObject( data ) ) { return; }

				_( data ).each( save_field_val );
			}

			function save_field_val( val, idx ) {
				//if ( 'appearance' == idx && ! val ) { return true; }
				model.set_property( idx, val );
			}

			this.fields.each( save_field );
		},

		// ========== PopupSettings_ItemGroup --- Render
		render: function() {
			Upfront.Views.Editor.Settings.Item.prototype.render.call(this);
			this.$el.find('.upfront-settings-item-content').addClass('clearfix');
		},

		// ========== PopupSettings_ItemGroup --- Register_change
		register_change: function() {
			this.fields.each(function(field) {
				field.property.set({'value': field.get_value()}, {'silent': false});
			});
			this.trigger( 'popup:settings:changed' );
		}

	});

	// Return the module object.
	return PopupSettings_ItemGroup;

});
})();