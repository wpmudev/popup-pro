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

		// ========== ItemGroup --- Update_fields  (internal function)
		update_fields: function update_fields( refresh_preview ) {
			refresh_preview = (true == refresh_preview);

			this.fields.each(function loop_fields( field ) {
				field.property.set(
					{'value': field.get_value()},
					{'silent': ! refresh_preview}
				);
			});
			this.trigger( 'popup:settings:changed' );
		},

		// ========== ItemGroup --- Preview_change
		preview_change: function preview_change() {
			this.update_fields( true );
		},

		// ========== ItemGroup --- Silent_change
		silent_change: function silent_change() {
			this.update_fields( false );
		}

	});

	// Return the module object.
	return ItemGroup;

});
})();