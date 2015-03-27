(function () {
define(
[
	_popup_uf_data.base_url + 'js/element-settings-itemgroup.js',
],
/**
 * The settings tab "Appearance"
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
	var PopupSettings_PanelAppearance = Upfront.Views.Editor.Settings.Panel.extend({

		// ========== PopupSettings_PanelAppearance --- Initialize
		initialize: function(opts) {
			var attr, ind,
				groups = [],
				me = this;

			this.options = opts;
			attr = {model: this.model};

			// Create the settings groups.
			groups[0] = new PopupSettings_Group_Appearance( attr );

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

		// ========== PopupSettings_PanelAppearance --- Render
		render: function() {
			function update_setting( setting ) {
				if ( setting.update ) {
					setting.update();
				}
			}

			Upfront.Views.Editor.Settings.Panel.prototype.render.call( this );

			this.$el.addClass( 'upfront_popup-settings-panel-appearance' );
			this.settings.each( update_setting );
		},

		// ========== PopupSettings_PanelAppearance --- Get_label
		get_label: function() {
			return l10n.tab_appearance;
		}

	});

	/**
	 * Appearance options of the PopUp.
	 */
	var PopupSettings_Group_Appearance = ItemGroup.extend({

		// ========== PopupSettings_Group_Appearance --- Initialize
		initialize: function() {
			var key, style,
				me = this,
				styles = [];

			// Prepare the styles-select-values.
			for ( key in Upfront.data.upfront_popup.styles ) {
				if ( ! Upfront.data.upfront_popup.styles.hasOwnProperty( key ) ) {
					continue;
				}

				style = Upfront.data.upfront_popup.styles[key];
				if ( style.deprecated ) { continue; }
				styles.push( {label: style.name, value: key} );
			}

			function did_change() {
				me.register_change( me );
			}

			// Collect all Display setting fields.
			this.fields = _([
				new Upfront.Views.Editor.Field.Select({
					model: this.model,
					property: 'popup__style',
					values: styles,
					change: did_change
				}),
				new Upfront.Views.Editor.Field.Checkboxes({
					model: this.model,
					property: 'popup__round_corners',
					values: [
						{ label: l10n.round_corners, value: 'yes' }
					],
					change: did_change
				}),
			]);
		},

		// ========== PopupSettings_Group_Appearance --- Get_title
		get_title: function() {
			return l10n.group_template;
		}

	});


	// Return the module object.
	return PopupSettings_PanelAppearance;

});
})();