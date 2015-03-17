(function() {
require(
[
    // No other modules required
],
/**
 * Contains the logic for the Upfront Editor.
 *
 * @since  4.8.0.0
 *
 * @param  string editor_style
 * @param  string public_style
 */
function() {

	/**
	 * Define the translations
	 */
	var l10n = Upfront.Settings.l10n.popup_element;

	/**
	 * Define the Popup Model.
	 */
	var PopupModel = Upfront.Models.ObjectModel.extend({

		// ========== Init
		init: function () {
			var properties = _.clone( Upfront.data.upfront_popup.defaults );

			properties.element_id = Upfront.Util.get_unique_id(
				properties.id_slug + '-object'
			);

			this.init_properties( properties );
		}

	});

	/**
	 * Define the Popup View that is displayed as inline block in edit mode.
	 */
	var PopupView = Upfront.Views.ObjectView.extend({
		markup: false,

		// ========== Initialize
		initialize: function() {
			var me = this;

			function property_changed( model ) {
				if ( ! model || ! model.get) { return true; }
				if ( 'row' !== model.get( 'name' ) ) {
					me.markup = false;
					me.render();
				}
			}

			if ( ! ( this.model instanceof PopupModel ) ) {
				this.model = new PopupModel({
					properties: this.model.get( 'properties' )
				});
			}

			Upfront.Views.ObjectView.prototype.initialize.call( this );

			this.model.get( 'properties' ).on( 'change', property_changed );
		},

		// ========== Render
		render: function() {
			if ( ! this.markup ) {
				var me = this,
					options = Upfront.Util.model_to_json( this.model ),
					data = {}
				;

				function markup_loaded( response ) {
					me.markup = response.data;
					Upfront.Views.ObjectView.prototype.render.call( me );
				}

				data['action'] = 'upfront-popup_element-get_markup';
				data['properties'] = options.properties;

				// Communicate with the server to get the markup.
				Upfront.Util
					.post( data )
					.done( markup_loaded );
			} else {
				Upfront.Views.ObjectView.prototype.render.call( this );
			}
		},

		// ========== On_render
		on_render: function() {
			var ueditor_config = {
					linebreaks: false,
					inserts: {},
					autostart: false
				};

			function ueditor_start() {
				var $swap = jQuery(this).find('.upfront-quick-swap');
				if ( $swap.length ) {
					$swap.remove();
				}
				me.model.set_property('is_edited', true, true);
				Upfront.Events.trigger('upfront:element:edit:start', 'text');
			}

			function ueditor_stop(){
				var ed = me.$el.find('.upfront-object-content').data('ueditor'),
					text = ''
				;

				try {
					text = ed.getValue(true);
				} catch (e) {
					text = '';
				}

				if ( text ) {
					me.model.set_content( text, {silent: true} );
				}

				Upfront.Events.trigger( 'upfront:element:edit:stop' );
				ed.redactor.events.trigger( 'cleanUpListeners' );
				me.render();
			}

			function ueditor_sync(){
				var text = jQuery.trim( jQuery(this).html() );

				if ( text ) {
					text = jQuery( text ).html();
					me.model.set_content( text, {silent: true} );
				}
			}

			this.$el.find( '.upfront-object-content' )
				.addClass( 'upfront-plain_txt' )
				.ueditor( ueditor_config )
				.on( 'start', ueditor_start )
				.on( 'stop', ueditor_stop )
				.on( 'syncAfter', ueditor_sync )
			;
		},

		// ========== Get_content_markup
		get_content_markup: function () {
			return !! this.markup ? this.markup : l10n.hold_on;
		}
	});

	/**
	 * Simply a collection of fields.
	 * This is the base-class of other objects (see below)
	 */
	var Popup_SettingsItem_ComplexItem = Upfront.Views.Editor.Settings.Item.extend({
		// ========== Save_fields
		save_fields: function () {
			var model = this.model;

			function save_field( field ) {
				var data = field.get_value();
				if ( !_.isObject( data ) ) { return; }

				_( data ).each( save_field_val );
			}

			function save_field_val( val, idx ) {
				if ( 'appearance' == idx && ! val ) { return true; }
				model.set_property( idx, val );
			}

			this.fields.each( save_field );
		}
	});

	/**
	 * This is the Settings master thingy.
	 * It contains both, the settings data (options) and the settings toolbox
	 * definitions (panel).
	 */
	var PopupSettings = Upfront.Views.Editor.Settings.Settings.extend({
		// ========== Initialize
		initialize: function (opts) {
			var panel;

			panel = new PopupSettings_Panel({model: this.model});

			this.has_tabs = false;
			this.options = opts;
			this.panels = _([
				panel
			]);
		},

		// ========== Get_title
		get_title: function () {
			return l10n.settings;
		}
	});

	/**
	 * The Settings_Panel defines all settings that that can be modified via the
	 * Upfront settings panel.
	 *
	 * It defines the input fields/types, panel-title, and other UI elements.
	 */
	var PopupSettings_Panel = Upfront.Views.Editor.Settings.Panel.extend({
		// ========== Initialize
		initialize: function (opts) {
			var me = this,
				attr, appearance, behavior, trigger
			;

			this.options = opts;
			attr = {model: this.model};

			appearance = new PopupSettings_Field_DisplayAppearance( attr );
			behavior = new PopupSettings_Field_DisplayBehavior( attr );
			trigger = new PopupSettings_Field_DisplayTrigger( attr );

			this.settings = _([
				appearance,
				behavior,
				trigger,
				new Upfront.Views.Editor.Settings.Item({
					model: this.model,
					title: 'Demo Panel',
					fields: [
						new Upfront.Views.Editor.Field.Checkboxes({
							//className: "upfront_popup-logout_style upfront-field-wrap upfront-field-wrap-multiple upfront-field-wrap-radios",
							model: this.model,
							property: 'logged_in_preview',
							label: "",
							values: [
								{ label: "Preview", value: 'yes' }
							],
							change: function() {
								this.property.set({'value': this.get_value()}, {'silent': false});
							}
						}),
						new Upfront.Views.Editor.Field.Radios({
							className: "upfront_popup-logout_style upfront-field-wrap upfront-field-wrap-multiple upfront-field-wrap-radios",
							model: this.model,
							property: "logout_style",

							values: [
								{label: 'Nothing', value: "nothing"},
								{label: 'Log Out Link', value: "link"}
							],
							change: function() {
								this.property.set({'value': this.get_value()}, {'silent': false});
							}
						}),
						new Upfront.Views.Editor.Field.Text({
							className: "upfront_popup-logout_text upfront-field-wrap upfront-field-wrap-text",
							model: this.model,
							property: 'logout_link',
							label: 'Log Out Link:',
							change: function() {
								this.property.set({'value': this.get_value()}, {'silent': false});
							}
						}),
					]
				})
			]);
			appearance.on( 'popup:appearance:changed', behavior.update, behavior );
			appearance.on( 'popup:appearance:changed', trigger.update, trigger );
			appearance.on( 'popup:appearance:changed', function () {
				me.trigger( 'upfront:settings:panel:refresh', me );
			})
		},

		// ========== Render
		render: function () {
			function update_setting( setting ) {
				if ( setting.update ) {
					setting.update();
				}
			}

			Upfront.Views.Editor.Settings.Panel.prototype.render.call( this );

			this.$el.addClass( 'upfront_popup-settings-panel' );
			this.settings.each( update_setting );
		},

		// ========== Get_label
		get_label: function () {
			return l10n.display;
		},

		// ========== Get_title
		get_title: function () {
			return l10n.display;
		}
	});

	/**
	 * (???)
	 * Unreviewed code...
	 */
	var PopupSettings_Field_DisplayBehavior = Upfront.Views.Editor.Settings.Item.extend({
		className: 'display_behavior',
		events: function () {
			return _.extend({},
				Upfront.Views.Editor.Settings.Item.prototype.events,
				{'click': 'register_change'}
			);
		},
		initialize: function () {
			var style = this.model.get_property_value_by_name("style");
			var hover_disabled = !style || "popup" == style;
			var behaviors = [
				{label: l10n.show_on_hover, value: "hover", disabled: hover_disabled},
				{label: l10n.show_on_click, value: "click"},
			];
			this.fields = _([
				new Upfront.Views.Editor.Field.Radios({
					model: this.model,
					property: "behavior",
					values: behaviors
				}),
			]);
		},
		render: function () {
			Upfront.Views.Editor.Settings.Item.prototype.render.call(this);
			this.$el
				.addClass("upfront_popup-item-display_behavior")
				.find(".upfront-settings-item-content").addClass("clearfix").end()
				.hide()
			;

		},
		get_title: function () {
			return "Show Drop-Down Form on:";
		},
		register_change: function () {
			this.fields.each(function (field) {
				field.property.set({'value': field.get_value()}, {'silent': false});
			});
			this.trigger("popup:behavior:changed");
		},
		update: function () {
			var style = this.model.get_property_value_by_name("style");
			this.initialize();
			this.$el.empty();
			this.render();
			if ("form" != style) this.$el.show();
		}
	});

	/**
	 * (???)
	 * Unreviewed code...
	 */
	var PopupSettings_Field_DisplayAppearance = Popup_SettingsItem_ComplexItem.extend({
		/*events: function () {
			return _.extend({},
				Upfront.Views.Editor.Settings.Item.prototype.events,
				{"change": "register_change"}
			);
		}*/
		initialize: function () {
			var me = this;
			var styles = [
				{label: l10n.on_page, value: "form"},
				{label: l10n.dropdown, value: "dropdown"},
				/*{label: l10n.in_lightbox, value: "popup"},*/
			];
			this.fields = _([
				new Upfront.Views.Editor.Field.Radios({
					model: this.model,
					property: "style",

					values: styles,
					change: function() { me.register_change(me) }
				}),
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'label_text',
					label: 'Log In Button:',
					change: function() { me.register_change(me) }
				}),
			]);
		},
		render: function () {
			Upfront.Views.Editor.Settings.Item.prototype.render.call(this);
			this.$el.find(".upfront-settings-item-content").addClass("clearfix");
		},
		get_title: function () {
			return l10n.appearance;
		},
		register_change: function () {

			this.fields.each(function (field) {
				field.property.set({'value': field.get_value()}, {'silent': false});
			});
			this.trigger("popup:appearance:changed");
		}
	});

	/**
	 * (???)
	 * Unreviewed code...
	 */
	var PopupSettings_Field_DisplayTrigger = Popup_SettingsItem_ComplexItem.extend({
		className: 'upfront_popup-item-display_trigger',
		initialize: function () {
			var me = this;
			this.fields = _([
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'trigger_text',
					label: 'Log In Trigger:',
					change: function() { me.register_change(me) }
				}),
			]);
		},
		register_change: function () {
			this.fields.each(function (field) {
				field.property.set({'value': field.get_value()}, {'silent': false});
			});
			//this.trigger("popup:behavior:changed");
		},
		update: function () {
			var style = this.model.get_property_value_by_name("style");
			this.initialize();
			this.$el.empty();
			this.render();
			if ("form" != style) this.$el.show();
		},
		render: function () {
			Upfront.Views.Editor.Settings.Item.prototype.render.call(this);
			this.$el
				.find(".upfront-settings-item-content").addClass("clearfix").end()
				.hide()
			;
			this.$el.find('.upfront-settings-item-title').remove();
		},
		get_title: function () {
			return "";
		}
	});

	/**
	 * Defines the element that is displayed in the "Draggable Elements" panel.
	 */
	var PopupElement = Upfront.Views.Editor.Sidebar.Element.extend({
		priority: 130,

		// ========== Render
		render: function () {
			this.$el.addClass( 'upfront-icon-element upfront-icon-element-popup' );
			this.$el.html( l10n.element_name );
		},

		// ========== Add_element
		add_element: function () {
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
			'Settings': PopupSettings,
			cssSelectors: {
				'.upfront_popup-form p': {label: l10n.css.containers, info: l10n.css.containers_info},
				'.upfront_popup-form form label': {label: l10n.css.labels, info: l10n.css.labels_info}
			},
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