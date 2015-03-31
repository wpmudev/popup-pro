(function() {
define(
[
	_popup_uf_data.base_url + 'js/element-model.js',
	_popup_uf_data.base_url + 'js/element-view.js',
	_popup_uf_data.base_url + 'js/element-settings.js',
],
/**
 * Contains the logic for the Upfront Editor.
 *
 * @since  4.8.0.0
 *
 * @param  The params are objects returned by the defined modules above.
 */
function( PopupModel, PopupView, PopupSettings ) {

	/**
	 * Define the translations
	 */
	var l10n = Upfront.Settings.l10n.popup_element;

	/**
	 * Defines the element that is displayed in the "Draggable Elements" panel.
	 */
	var PopupElement = Upfront.Views.Editor.Sidebar.Element.extend({
		priority: 130,

		// ========== Render
		render: function() {
			this.$el.addClass( 'upfront-icon-element upfront-icon-element-popup' );
			this.$el.html( l10n.element_name );
		},

		// ========== Add_element
		add_element: function() {
			var object = new PopupModel(),
				module = new Upfront.Models.Module({
					name: '',
					properties: [
						{'name': 'element_id', 'value': Upfront.Util.get_unique_id('module')},
						{'name': 'class', 'value': 'c24 upfront-popup_element-module'},
						{'name': 'has_settings', 'value': 0},
						{'name': 'row', 'value': Upfront.Util.height_to_row(210)}
					],
					objects: [object]
				})
			;

			this.add_module( module );
		}
	});

	/**
	 * Register the new upfront element.
	 *
	 * This function ties everything together and delivers the whole package to
	 * Upfront. After this function call Upfront knows about out new module! :)
	 */
	Upfront.Application.LayoutEditor.add_object(
		'PopUp',
		{
			'Model': PopupModel,
			'View': PopupView,
			'Element': PopupElement,

			// Define the settings panel.
			'Settings': PopupSettings,

			// Definitions for the built-in CSS editor.
			cssSelectors: {
				'.wdpu-head': {label: l10n.css.header_label, info: l10n.css.header_info},
				'.wdpu-title': {label: l10n.css.title_label, info: l10n.css.title_info},
				'.wdpu-subtitle': {label: l10n.css.subtitle_label, info: l10n.css.subtitle_info},
				'.wdpu-cta': {label: l10n.css.cta_label, info: l10n.css.cta_info},
				'.wdpu-buttons': {label: l10n.css.buttons_label, info: l10n.css.buttons_info},
				'.wdpu-text': {label: l10n.css.outer_content_label, info: l10n.css.outer_content_info},
				'.wdpu-content': {label: l10n.css.content_label, info: l10n.css.content_info},
				'.wdpu-container': {label: l10n.css.popup_label, info: l10n.css.popup_info}
			},
			cssSelectorsId: 'PopupModel'
		}
	);

	/**
	 * We also need to manually register an official Alias for our model/view
	 * in the Upfront Collections.
	 *
	 * I.e. We tell Upfront that "PopupModel" is handled by our module.
	 */
	Upfront.Models.PopupModel = PopupModel;
	Upfront.Views.PopupView = PopupView;

});
})();