(function () {
define(
[
	_popup_uf_data.base_url + 'js/element-settings-contents.js' + _popup_uf_data.cache_ver,
	_popup_uf_data.base_url + 'js/element-settings-appearance.js' + _popup_uf_data.cache_ver,
	_popup_uf_data.base_url + 'js/element-settings-behavior.js' + _popup_uf_data.cache_ver,
	_popup_uf_data.base_url + 'js/element-settings-rules.js' + _popup_uf_data.cache_ver,
],
/**
 * Settings module defines the element settings.
 *
 * @since  4.8.0.0
 */
function( PanelContents, PanelAppearance, PanelBehavior, PanelRules ) {

	/**
	 * Define the translations
	 */
	var l10n = Upfront.Settings.l10n.popup_element;

	/**
	 * This is the main Settings object.
	 * It contains both, the settings data (options) and the settings toolbox
	 * definitions (panel).
	 */
	var PopupSettings = Upfront.Views.Editor.Settings.Settings.extend({
		// ========== Initialize
		initialize: function( opts ) {
			var appearance, contents;

			contents = new PanelContents({model: this.model});
			appearance = new PanelAppearance({model: this.model});
			behavior = new PanelBehavior({model: this.model});
			rules = new PanelRules({model: this.model});

			this.has_tabs = true;
			this.options = opts;
			this.panels = _([
				contents,
				appearance,
				behavior,
				rules,
			]);
		},

		// ========== Get_title
		get_title: function() {
			return l10n.settings;
		}
	});

	// Return the module object.
	return PopupSettings;

});
})();