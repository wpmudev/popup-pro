(function _define_field_select() {
define(
[
	// No dependencies
],
/**
 * Defines the custom input field: Select-List Field
 * This is a modification of the default Upfront Select field that allows nested
 * options, simulating <optgroup> behavior.
 *
 * @since  4.8.0.0
 */
function _load_field_select() {

	/**
	 * Custom Field Definition: ImageField
	 * This field can be used to select a single image.
	 *
	 * Example:
	 *
	 * var options = [
	 *     { value: 'simple', label: 'Simple value', disabled: false, icon: '' },
	 *     { label: 'Group', items: [
	 *         { value: 'sub1', label: 'Sub-item 1' },
	 *         { value: 'sub2', label: 'Sub-item 2' },
	 *         { label: 'Sub-Group', items: [
	 *             { value: 'sub3_1', label: 'Sub-item 3' },
	 *             { value: 'sub3_2', label: 'Sub-item 4' }
	 *         ] }
	 *     ] }
	 * ];
	 */
	var SelectField = Upfront.Views.Editor.Field.Select.extend({

		initialize: function initialize( opts ) {
			var me = this;

			if ( opts.values_array && ! opts.values ) {
				opts.values = me.convert_array( opts.values_array );
			}

			Upfront.Views.Editor.Field.Field.prototype.initialize.call( me, opts );
		},

		/**
		 * Renders a single select-item or a select-group (if the item is a
		 * group item)
		 */
		get_value_html: function get_value_html( item, index ) {

			function increase_level( subitem ) {
				if ( item.label ) {
					subitem.level = item.level + 1;
				} else {
					subitem.level = item.level;
				}

				return subitem;
			}

			if ( isNaN( item.level ) ) {
				item.level = 0;
			}

			var id = this.get_field_id() + '-' + index,
				children = '',
				code = '',
				style = 'padding-left:' + (item.level * 24) + 'px',
				attr = {},
				saved_value = this.get_saved_value(),
				classes = 'upfront-field-select-option uf-select-level-' + item.level,
				icon_class = this.options.icon_class ? this.options.icon_class : null;

			if ( item.items ) {
				item.items = _.map(
					item.items,
					increase_level,
					this
				);

				children = _.map(
					item.items,
					this.get_value_html,
					this
				).join( '' );

				item.disabled = true;
			}

			attr['type']  = this.multiple ? 'checkbox' : 'radio';
			attr['id']    = id;
			attr['name']  = this.get_field_name();
			attr['class'] = 'upfront-field-' + ( this.multiple ? 'checkbox' : 'radio' );
			attr['value'] = item.value;

			if ( item.disabled ) {
				attr['disabled'] = 'disabled';
				classes += ' upfront-field-select-option-disabled';
			}

			if ( this.multiple && _.contains( saved_value, item.value ) ) {
				attr['checked'] = 'checked';
			} else if ( ! this.multiple && saved_value == item.value ) {
				attr['checked'] = 'checked';
			}

			if ( attr['checked'] ) {
				classes += ' upfront-field-select-option-selected';
			}

			if ( item.label ) {
				code =
				'<li class="' + classes + '" style="' + style + '">' +
					'<label for="' + id + '">' +
						this.get_icon_html( item.icon, icon_class ) +
						'<span class="upfront-field-label-text">' + item.label + '</span>' +
					'</label>' +
					'<input ' + this.get_field_attr_html( attr ) + ' />' +
				'</li>';
			}

			code += children;

			return code;
		},

		/**
		 * Converts a normal key->value based array to an argument list that
		 * can be used for the SelectField value param.
		 */
		convert_array: function convert_array( array ) {
			var result = [];

			function convert_item( data, index ) {
				if ( 'object' == typeof data ) {
					return {
						label: index,
						items: this.convert_array( data )
					};
				} else {
					return {
						value: index,
						label: data
					};
				}
			}

			result = _.map( array, convert_item, this );

			return result;
		}

	});

	// Return the module object.
	return SelectField;

});
})();