(function _define_settings_behavior() {
define(
[
	_popup_uf_data.base_url + 'js/element-field-itemgroup.js' + _popup_uf_data.cache_ver,
],
/**
 * The settings tab "Behavior"
 *
 * @since  4.8.0.0
 */
function _load_settings_behavior( ItemGroup ) {

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
	var PopupSettings_PanelBehavior = Upfront.Views.Editor.Settings.Panel.extend({

		// ========== PopupSettings_PanelBehavior --- Initialize
		initialize: function initialize( opts ) {
			var attr, ind,
				groups = [],
				me = this;

			me.options = opts;
			attr = {model: me.model};

			// Create the settings groups.
			groups[0] = new PopupSettings_Group_AppearsOn( attr );
			groups[1] = new PopupSettings_Group_AppearsOnDelay( attr );
			groups[2] = new PopupSettings_Group_AppearsOnScroll( attr );
			groups[3] = new PopupSettings_Group_AppearsOnAnchor( attr );
			groups[4] = new PopupSettings_Group_AppearsOnClick( attr );
			groups[5] = new PopupSettings_Group_Hide( attr );
			groups[6] = new PopupSettings_Group_Closing( attr );
			groups[7] = new PopupSettings_Group_Forms( attr );

			// Assign groups to the panel.
			me.settings = _( groups );

			// Add event handlers to the groups.
			for ( ind = 0; ind < groups.length; ind += 1 ) {
				groups[ind].on( 'popup:settings:changed', groups[ind].update, groups[ind] );
				groups[ind].on( 'popup:settings:changed', function on_popup_settings_changed() {
					me.trigger( 'upfront:settings:panel:refresh', me );
				});
			}

		},

		// ========== PopupSettings_PanelBehavior --- Render
		render: function render() {
			function update_setting( setting ) {
				if ( setting.update ) {
					setting.update();
				}
			}

			Upfront.Views.Editor.Settings.Panel.prototype.render.call( this );

			this.$el.addClass( 'upfront_popup-settings-panel-behavior' );
			this.settings.each( update_setting );
		},

		// ========== PopupSettings_PanelBehavior --- Get_label
		get_label: function get_label() {
			return l10n.tab_behavior;
		}

	});

	/**
	 * Display-Type option.
	 */
	var PopupSettings_Group_AppearsOn = ItemGroup.extend({

		// ========== PopupSettings_Group_AppearsOn --- Initialize
		initialize: function initialize() {
			var me = this,
				display_options;

			function preview_change() { me.preview_change(); me.show_detail_fields(); }
			function silent_change() { me.silent_change(); me.show_detail_fields(); }

			display_options = [
				{value:'delay', label:l10n.display_option_delay},
				{value:'scroll', label:l10n.display_option_scroll},
				{value:'anchor', label:l10n.display_option_anchor},
				{value:'leave', label:l10n.display_option_leave},
				{value:'click', label:l10n.display_option_click}
			];

			this.fields = _([
				new Upfront.Views.Editor.Field.Select({
					model: this.model,
					values: display_options,
					property: 'popup__display',
					label: l10n.display_option,
					change: silent_change
				})
			]);
		},

		// ========== PopupSettings_Group_AppearsOn --- Render
		render: function render() {
			var me = this;

			ItemGroup.prototype.render.call( me );

			// Wait so all other panels can be rendered before calling the
			// function show_detail_fields.
			window.setTimeout(function() {
				me.show_detail_fields();
			}, 25);
		},

		// ========== PopupSettings_Group_AppearsOn --- Get_title
		get_title: function get_title() {
			return l10n.group_appears_on;
		},

		// ========== PopupSettings_Group_AppearsOn --- Show_detail_fields
		show_detail_fields: function show_detail_fields() {
			var me = this,
				panel = me.$el.closest( '.upfront-settings_panel' ),
				mode = me.model.get_property_value_by_name( 'popup__display' );

			panel.find( '.conf-display' ).hide();
			panel.find( '.conf-display-' + mode ).show();

			//@TODO: How to refresh the panel height now?
		}

	});

	/**
	 * Display options for: Delay
	 */
	var PopupSettings_Group_AppearsOnDelay = ItemGroup.extend({

		// ========== group: False means that we don't want a title
		group: false,

		// ========== className: CSS Class that is added to the ItemGroup
		className: 'conf-display conf-display-delay',

		// ========== PopupSettings_Group_AppearsOnDelay --- Initialize
		initialize: function initialize() {
			var me = this,
				delay_types;

			function preview_change() { me.preview_change(); }
			function silent_change() { me.silent_change(); }

			delay_types = [
				{value:'s', label:l10n.delay_type_sec},
				{value:'m', label:l10n.delay_type_min}
			];

			this.fields = _([
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__display_data__delay',
					label: l10n.display_delay,
					change: silent_change
				}),
				new Upfront.Views.Editor.Field.Select({
					model: this.model,
					property: 'popup__display_data__delay_type',
					values: delay_types,
					change: silent_change
				})
			]);
		}

	});

	/**
	 * Display options for: Scroll.
	 */
	var PopupSettings_Group_AppearsOnScroll = ItemGroup.extend({

		// ========== group: False means that we don't want a title
		group: false,

		// ========== className: CSS Class that is added to the ItemGroup
		className: 'conf-display conf-display-scroll',

		// ========== PopupSettings_Group_AppearsOnScroll --- Initialize
		initialize: function initialize() {
			var me = this,
				scroll_types;

			function preview_change() { me.preview_change(); }
			function silent_change() { me.silent_change(); }

			scroll_types = [
				{value:'%', label:l10n.scroll_type_percent},
				{value:'px', label:l10n.scroll_type_px}
			];

			this.fields = _([
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__display_data__scroll',
					label: l10n.display_scroll,
					change: silent_change
				}),
				new Upfront.Views.Editor.Field.Select({
					model: this.model,
					property: 'popup__display_data__scroll_type',
					values: scroll_types,
					change: silent_change
				})
			]);
		}

	});

	/**
	 * Display options for: Anchor.
	 */
	var PopupSettings_Group_AppearsOnAnchor = ItemGroup.extend({

		// ========== group: False means that we don't want a title
		group: false,

		// ========== className: CSS Class that is added to the ItemGroup
		className: 'conf-display conf-display-anchor',

		// ========== PopupSettings_Group_AppearsOnAnchor --- Initialize
		initialize: function initialize() {
			var me = this;

			function preview_change() { me.preview_change(); }
			function silent_change() { me.silent_change(); }

			this.fields = _([
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__display_data__anchor',
					label: l10n.display_anchor,
					placeholder: l10n.hint_selector,
					change: silent_change
				})
			]);
		}

	});

	/**
	 * Display options for: Click.
	 */
	var PopupSettings_Group_AppearsOnClick = ItemGroup.extend({

		// ========== group: False means that we don't want a title
		group: false,

		// ========== className: CSS Class that is added to the ItemGroup
		className: 'conf-display conf-display-click',

		// ========== PopupSettings_Group_AppearsOnClick --- Initialize
		initialize: function initialize() {
			var me = this;

			function preview_change() { me.preview_change(); }
			function silent_change() { me.silent_change(); }

			this.fields = _([
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__display_data__click',
					label: l10n.display_click,
					placeholder: l10n.hint_selector,
					change: silent_change
				}),
				new Upfront.Views.Editor.Field.Checkboxes({
					model: this.model,
					property: 'popup__display_data__click_multi',
					values: [
						{ label: l10n.click_repeat, value: 'yes' }
					],
					change: silent_change
				})
			]);
		}

	});

	/**
	 * Do-Not-Show-Popup-Again options.
	 */
	var PopupSettings_Group_Hide = ItemGroup.extend({

		// ========== PopupSettings_Group_Hide --- Initialize
		initialize: function initialize() {
			var me = this;

			function preview_change() { me.preview_change(); }
			function silent_change() { me.silent_change(); }

			this.fields = _([
				new Upfront.Views.Editor.Field.Checkboxes({
					model: this.model,
					property: 'popup__can_hide',
					values: [
						{ label: l10n.hide_button, value: 'yes' }
					],
					change: preview_change
				}),
				new Upfront.Views.Editor.Field.Checkboxes({
					model: this.model,
					property: 'popup__close_hides',
					values: [
						{ label: l10n.hide_always, value: 'yes' }
					],
					change: silent_change
				}),
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__hide_expire',
					label: l10n.hide_expire,
					change: silent_change
				})
			]);
		},

		// ========== PopupSettings_Group_Hide --- Get_title
		get_title: function get_title() {
			return l10n.group_hide;
		}

	});

	/**
	 * Closing condition options.
	 */
	var PopupSettings_Group_Closing = ItemGroup.extend({

		// ========== PopupSettings_Group_Closing --- Initialize
		initialize: function initialize() {
			var me = this;

			function preview_change() { me.preview_change(); }
			function silent_change() { me.silent_change(); }

			this.fields = _([
				new Upfront.Views.Editor.Field.Checkboxes({
					model: this.model,
					property: 'popup__overlay_close',
					values: [
						{ label: l10n.overlay_close, value: 'yes' }
					],
					change: silent_change
				})
			]);
		},

		// ========== PopupSettings_Group_Closing --- Get_title
		get_title: function get_title() {
			return l10n.group_closing;
		}

	});

	/**
	 * Form submit option.
	 */
	var PopupSettings_Group_Forms = ItemGroup.extend({

		// ========== PopupSettings_Group_Forms --- Initialize
		initialize: function initialize() {
			var me = this,
				submit_options;

			function preview_change() { me.preview_change(); me.show_detail_fields(); }
			function silent_change() { me.silent_change(); me.show_detail_fields(); }

			submit_options = [
				{value:'default', label:l10n.form_submit_default},
				{value:'ignore', label:l10n.form_submit_ignore},
				{value:'redirect', label:l10n.form_submit_redirect},
			];

			this.fields = _([
				new Upfront.Views.Editor.Field.Select({
					model: this.model,
					values: submit_options,
					property: 'popup__form_submit',
					label: l10n.form_submit,
					change: silent_change
				})
			]);
		},

		// ========== PopupSettings_Group_Forms --- Get_title
		get_title: function get_title() {
			return l10n.group_forms;
		}

	});

	// Return the module object.
	return PopupSettings_PanelBehavior;

});
})();