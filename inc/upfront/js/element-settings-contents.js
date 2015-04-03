(function _define_settings_contents() {
define(
[
	_popup_uf_data.base_url + 'js/element-settings-itemgroup.js',
],
/**
 * The settings tab "Contents"
 *
 * @since  4.8.0.0
 */
function _load_settings_contents( ItemGroup ) {

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
	var PopupSettings_PanelContents = Upfront.Views.Editor.Settings.Panel.extend({

		// ========== PopupSettings_PanelContents --- Initialize
		initialize: function initialize( opts ) {
			var attr, ind,
				groups = [],
				me = this;

			this.options = opts;
			attr = {model: this.model};

			// Create the settings groups.
			groups[0] = new PopupSettings_Group_Title( attr );
			groups[1] = new PopupSettings_Group_Cta( attr );

			// Assign groups to the panel.
			this.settings = _( groups );

			// Add event handlers to the groups.
			for ( ind = 0; ind < groups.length; ind += 1 ) {
				groups[ind].on( 'popup:settings:changed', groups[ind].update, groups[ind] );
				groups[ind].on( 'popup:settings:changed', function on_popup_settings_changed() {
					me.trigger( 'upfront:settings:panel:refresh', me );
				});
			}

		},

		// ========== PopupSettings_PanelContents --- Render
		render: function render() {
			function update_setting( setting ) {
				if ( setting.update ) {
					setting.update();
				}
			}

			Upfront.Views.Editor.Settings.Panel.prototype.render.call( this );

			this.$el.addClass( 'upfront_popup-settings-panel-contents' );
			this.settings.each( update_setting );
		},

		// ========== PopupSettings_PanelContents --- Get_label
		get_label: function get_label() {
			return l10n.tab_contents;
		}

	});

	/**
	 * PopUp Title settings.
	 */
	var PopupSettings_Group_Title = ItemGroup.extend({

		// ========== PopupSettings_Group_Title --- Initialize
		initialize: function initialize() {
			var me = this;

			function did_change() {
				me.register_change( me );
			}

			this.fields = _([
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__title',
					placeholder: l10n.title,
					change: did_change
				}),
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__subtitle',
					placeholder: l10n.subtitle,
					change: did_change
				}),
			]);
		},

		// ========== PopupSettings_Group_Title --- Get_title
		get_title: function get_title() {
			return l10n.group_title;
		}

	});

	/**
	 * Call To Action settings.
	 */
	var PopupSettings_Group_Cta = ItemGroup.extend({

		// ========== PopupSettings_Group_Cta --- Initialize
		initialize: function initialize() {
			var me = this;

			function did_change() {
				me.register_change( me );
			}

			this.fields = _([
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__cta_label',
					placeholder: l10n.cta_label,
					change: did_change
				}),
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__cta_link',
					placeholder: l10n.cta_link,
					change: did_change
				}),
			]);
		},

		// ========== PopupSettings_Group_Cta --- Get_title
		get_title: function get_title() {
			return l10n.group_cta;
		}

	});

	// Return the module object.
	return PopupSettings_PanelContents;

});
})();