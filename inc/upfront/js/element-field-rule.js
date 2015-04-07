/**
 * This custom field is very specialized for the PopUp element, as it even
 * contains some hardcoded property names...
 *
 * If you want to use this field in another context then you NEED to make a
 * copy of the file and adjust the code to match your needs!
 */

(function _define_field_rule() {
define(
[
	'text!' + _popup_uf_data.base_url + 'tpl/templates.html'
],
/**
 * Defines the custom input field: Rule Field
 *
 * @since  4.8.0.0
 */
function _load_field_rule( tpl_source ) {

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
		tpl_rule_field = templates.filter( '#wdpu-rule-field' );

	/**
	 * Custom Field Definition: RuleField
	 * This field can be used to add a new rule in the settings.
	 */
	var RuleField = Upfront.Views.Editor.Field.Field.extend({

		// ===== Field HTML template.
		template: _.template(
			tpl_rule_field.html()
		),

		// ===== Field event handlers.
		events: {
			'click .wdpu-rule' : 'on_change',
			'click .wdpu-details' : 'on_details'
		},

		// ========== RuleField --- Render
		render: function render() {
			var me = this,
				rule_fields = [],
				i = 0,
				code = '',
				prp_rules = me.model.get_property_by_name( 'popup__rule' ),
				active = prp_rules.get( 'value' );

			for ( i = 0; i < Upfront.data.upfront_popup.rules.length; i += 1 ) {
				var tpl_args, data, field;

				data = Upfront.data.upfront_popup.rules[i];
				tpl_args = {
					field_name: 'rule',
					field_id: 'rule-' + data.key,
					property: 'popup__rule',
					key: data.key,
					label: data.label,
					l10n: l10n
				};

				field = me.template( tpl_args );
				rule_fields.push( field );
			}

			code = rule_fields.join( '' );
			me.$el.html( code );

			// Set active rules to active status in UI.
			for ( i = 0; i < active.length; i += 1 ) {
				var el = me.$el.find( '.wdpu-rule[value="' + active[i] + '"]' );
				el.prop( 'checked', true );
			}
			me.update_states();
		},

		// ========== RuleField --- On_details
		on_details: function on_details( ev ) {
			var me = this,
				data = get_data_from_element( ev.target )
				view = me.options.parent.panel.parent_view.for_view.$el,
				form = view.find( '.forms #po-rule-' + data.key );

			ev.preventDefault();

			wpmUi.popup()
				.title( data.label )
				.modal( true )
				.content( form )
				.set_class( 'wdpu-rule-detail' )
				.show();
		},

		// ========== RuleField --- Update_states
		/**
		 * This function shows/hides the details-button and disables excluded
		 * rules depending on the rule state.
		 */
		update_states: function update_states() {
			var me = this,
				i = 0,
				rule = null;

			for ( i = 0; i < Upfront.data.upfront_popup.rules.length; i += 1 ) {
				var rule = Upfront.data.upfront_popup.rules[i],
					row = me.$el.find( '.rule-item-' + rule.key ),
					active = row.find( '.wdpu-rule:checked' ).length,
					details = row.find( '.wdpu-details' ),
					excl = me.$el.find( '.rule-item-' + rule.exclude );

				if ( active ) {
					details.show();
					excl.addClass( 'excluded' );
				} else {
					details.hide();
					excl.removeClass( 'excluded' );
				}
			}
		},

		// ========== RuleField --- On_change
		on_change: function on_change( ev ) {
			var me = this,
				i = 0,
				fields = null,
				active = [],
				data = [];

			me.update_states();

			fields = me.$el.find( '.wdpu-rule:checked' );
			for ( i = 0; i < fields.length; i += 1 ) {
				active.push( jQuery( fields[i] ).val() );
			}

			// Save the rule details.
			me.trigger( 'changed', active, data );
		}

	});

	/**
	 * Returns the rule-data object for the given html-element.
	 */
	function get_data_from_element( el ) {
		var item = jQuery( el ).closest( '.wdpu-rule-field' ),
			key = item.find( '.wdpu-rule' ).val()
			res = null
			i = 0;

		for ( i = 0; i < Upfront.data.upfront_popup.rules.length; i += 1 ) {
			var data;

			data = Upfront.data.upfront_popup.rules[i];

			if ( key == data.key ) {
				res = data;
				break;
			}
		}

		return res;
	}

	// Return the module object.
	return RuleField;

});
})();