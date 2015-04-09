(function _define_field_itemgroup() {
define(
[
	_popup_uf_data.base_url + 'js/element-field-image.js',
	_popup_uf_data.base_url + 'js/element-field-rule.js',
	_popup_uf_data.base_url + 'js/element-field-select.js'
],
/**
 * Defines the settings item-group base class
 *
 * @since  4.8.0.0
 */
function _load_field_itemgroup( ImageField, RuleField, SelectField ) {

	/**
	 * Simply a collection of fields.
	 * This is the base-class of setting fields.
	 *
	 * We also add custom field-types here that can be used by child classes via
	 * the `this` scope. E.g.
	 * var img_field = new this.Fields.ImageField( {...} );
	 */
	var ItemGroup = Upfront.Views.Editor.Settings.Item.extend({

		// ===== Fields object
		Fields: {
			ImageField: ImageField,
			RuleField: RuleField,
			SelectList: SelectField
		},

		// ========== ItemGroup --- Render
		render: function render() {
			var me = this;

			Upfront.Views.Editor.Settings.Item.prototype.render.call( me );
			me.$el.find('.upfront-settings-item-content').addClass( 'clearfix' );

			if ( me.className ) {
				me.$el.addClass( me.className );
			}
		},

		// ========== ItemGroup --- Register_change
		register_change: function register_change() {
			this.fields.each(function loop_fields( field ) {
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