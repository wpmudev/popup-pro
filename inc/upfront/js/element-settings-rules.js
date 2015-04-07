(function () {
define(
[
	_popup_uf_data.base_url + 'js/element-field-itemgroup.js',
],
/**
 * The settings tab "Behavior"
 *
 * @since  4.8.0.0
 */
function( ItemGroup ) {

	/**
	 * Define the translations.
	 *
	 * NOTE: This uses the .settings sub-collection of the l10n data!!
	 */
	var l10n = Upfront.Settings.l10n.popup_element.settings;

	/**
	 * The Settings_Panel defines all settings that that can be modified via the
	 * Upfront settings panel.
	 *
	 * It defines the input fields/types, panel-title, and other UI elements.
	 */
	var PopupSettings_PanelRules = Upfront.Views.Editor.Settings.Panel.extend({

		// ========== PopupSettings_PanelRules --- Initialize
		initialize: function( opts ) {
			var attr, ind,
				groups = [],
				me = this;

			this.options = opts;
			attr = {model: this.model};

			// Create the settings groups.
			groups[0] = new PopupSettings_Group_Rules( attr );

			// Assign groups to the panel.
			this.settings = _( groups );

			// Add event handlers to the groups.
			for ( ind = 0; ind < groups.length; ind += 1 ) {
				groups[ind].on( 'popup:settings:changed', groups[ind].update, groups[ind] );
				groups[ind].on( 'popup:settings:changed', function() {
					me.trigger( 'upfront:settings:panel:refresh', me );
				});
			}

		},

		// ========== PopupSettings_PanelRules --- Render
		render: function() {
			function update_setting( setting ) {
				if ( setting.update ) {
					setting.update();
				}
			}

			Upfront.Views.Editor.Settings.Panel.prototype.render.call( this );

			this.$el.addClass( 'upfront_popup-settings-panel-rules' );
			this.settings.each( update_setting );
		},

		// ========== PopupSettings_PanelRules --- Get_label
		get_label: function() {
			return l10n.tab_rules;
		}

	});

	/**
	 * Call To Action settings.
	 */
	var PopupSettings_Group_Rules = ItemGroup.extend({

		// ========== PopupSettings_Group_Rules --- Initialize
		initialize: function() {
			var me = this;

			function did_change_rule( rule, data ) {
				var prp_rule = this.model.get_property_by_name( 'popup__rule' ),
					prp_data = this.model.get_property_by_name( 'popup__rule_data' );

				prp_rule.set(
					{'value': rule},
					{'silent': true}
				);
				prp_data.set(
					{'value': data},
					{'silent': true}
				);

				this.trigger( 'popup:settings:changed' );
			}

			this.fields = _([
				new me.Fields.RuleField({
					model: me.model,
					change: did_change_rule,
					parent: me
				})
			]);
		},

		// ========== PopupSettings_Group_Rules --- Get_title
		get_title: function() {
			return l10n.group_rules;
		}

	});

	// Return the module object.
	return PopupSettings_PanelRules;

});
})();