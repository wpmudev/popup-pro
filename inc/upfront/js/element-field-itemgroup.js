(function _define_field_itemgroup() {
define(
[
	_popup_uf_data.base_url + 'js/element-field-image.js'
],
/**
 * Defines the settings item-group base class
 *
 * @since  4.8.0.0
 */
function _load_field_itemgroup( ImageField ) {

	/**
	 * Simply a collection of fields.
	 * This is the base-class of setting fields.
	 *
	 * We also add custom field-types here that can be used by child classes via
	 * the `this` scope. E.g.
	 * var img_field = new this.Fields.ImageField( {...} );
	 */
	var ItemGroup = Upfront.Views.Editor.Settings.Item.extend({

		// ===== ImageField object
		Fields: {
			ImageField: ImageField
		},

		// ========== ItemGroup --- Render
		render: function render() {
			Upfront.Views.Editor.Settings.Item.prototype.render.call( this );
			this.$el.find('.upfront-settings-item-content').addClass( 'clearfix' );
		},

		// ========== ItemGroup --- Register_change
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
	return ItemGroup;

});
})();