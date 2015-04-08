(function _define_settings_appearance() {
define(
[
	_popup_uf_data.base_url + 'js/element-field-itemgroup.js',
],
/**
 * The settings tab "Appearance"
 *
 * @since  4.8.0.0
 */
function _load_settings_appearance( ItemGroup ) {

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
		initialize: function initialize( opts ) {
			var attr, ind,
				groups = [],
				me = this;

			this.options = opts;
			attr = {model: this.model};

			// Create the settings groups.
			groups[0] = new PopupSettings_Group_Appearance( attr );
			groups[1] = new PopupSettings_Group_Image( attr );

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

		// ========== PopupSettings_PanelAppearance --- Render
		render: function render() {
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
		get_label: function get_label() {
			return l10n.tab_appearance;
		}

	});

	/**
	 * Appearance options of the PopUp.
	 */
	var PopupSettings_Group_Appearance = ItemGroup.extend({

		// ========== PopupSettings_Group_Appearance --- Initialize
		initialize: function initialize() {
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
				})
			]);
		},

		// ========== PopupSettings_Group_Appearance --- Get_title
		get_title: function get_title() {
			return l10n.group_template;
		}

	});

	/**
	 * Appearance options of the PopUp.
	 */
	var PopupSettings_Group_Image = ItemGroup.extend({

		// ========== PopupSettings_Group_Image --- Initialize
		initialize: function initialize() {
			var key, style,
				me = this,
				styles = [];

			function did_change() {
				me.register_change( me );
			}

			// Collect all Display setting fields.
			this.fields = _([
				new this.Fields.ImageField({
					model: this.model,
					property: 'popup__image',
					change: did_change
				}),
				new Upfront.Views.Editor.Field.Radios({
					model: this.model,
					property: 'popup__image_pos',
					label: '',
					layout: 'vertical',
					values: [
						{ label: l10n.pos_left, value: 'left', icon: 'pos-left' },
						{ label: l10n.pos_right, value: 'right', icon: 'pos-right' }
					],
					change: did_change
				}),
				new Upfront.Views.Editor.Field.Checkboxes({
					model: this.model,
					property: 'popup__image_not_mobile',
					values: [
						{ label: l10n.image_not_mobile, value: 'yes' }
					],
					change: did_change
				})
			]);
		},

		// ========== PopupSettings_Group_Image --- Get_title
		get_title: function get_title() {
			return l10n.group_image;
		}

	});


	// Return the module object.
	return PopupSettings_PanelAppearance;

});
})();