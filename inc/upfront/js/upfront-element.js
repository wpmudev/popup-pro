(function($) {
/*
 * @todo: What is the first param of define() doing?
 */
    require(
[
    //"upfront/upfront-application"
    //"scripts/upfront/upfront-views-editor"
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
		init: function () {
			//var properties = _.clone( Upfront.data.upfront_popup.defaults );
			properties.element_id = Upfront.Util.get_unique_id( properties.id_slug + '-object' );
			this.init_properties( properties );
		}
	});

	/**
	 * Define the Popup View that is displayed as inline block in edit mode.
	 */
	var PopupView = Upfront.Views.ObjectView.extend({
		markup: false,

		initialize: function() {
			if ( ! ( this.model instanceof PopupModel ) ) {
				this.model = new PopupModel(
					{
						properties: this.model.get( 'properties' )
					}
				);
			}

			Upfront.Views.ObjectView.prototype.initialize.call( this );
			var me = this;

			this.model.get( 'properties' ).on( 'change', function ( model ) {
				if ( ! model || ! model.get) { return true; }
				if ( 'row' !== model.get('name') ) {
					me.markup = false;
					me.render();
				}
			});
		},

		render: function() {
			if ( ! this.markup ) {
				var me = this,
					options = Upfront.Util.model_to_json( this.model )
				;

				// Communicate with the server to get the markup.
				Upfront.Util.post({
					'action': 'upfront-popup_element-get_markup',
					properties: options.properties
				}).done(function( response ) {
					me.markup = response.data;
					Upfront.Views.ObjectView.prototype.render.call( me );
				});
			}
			Upfront.Views.ObjectView.prototype.render.call( this );
		},
		get_content_markup: function () {
			return !! this.markup ? this.markup : l10n.hold_on;
		}
	});

	/**
	 * (???)
	 * Unreviewed code...
	 */
	var Popup_Fields_FieldAppearance_Icon_Image = Upfront.Views.Editor.Field.Text.extend({
		className: 'upfront-field-wrap upfront-field-wrap-appearance-icon-image',
		get_field_html: function () {
			return '<div class="upfront_popup-icon">' +
					'<img src="' + Upfront.data.upfront_popup.root_url + 'img/icon.png" />' +
					Upfront.Views.Editor.Field.Text.prototype.get_field_html.call(this) +
				'</div>'
			;
		},
		get_saved_value: function () {
			var prop = this.property ? this.property.get('value') : (this.model ? this.model.get(this.name) : '');
			return 'icon' === prop ? '' : prop;
		},
		get_value: function () {
			return this.$el.find("input").val() || 'icon';
		}
	});

	/**
	 * (???)
	 * Unreviewed code...
	 */
	var Popup_SettingsItem_ComplexItem = Upfront.Views.Editor.Settings.Item.extend({
		save_fields: function () {
			var model = this.model;
			this.fields.each(function (field) {
				var data = field.get_value();
				if (!_.isObject(data)) return;
				_(data).each(function (val, idx) {
					if ('appearance' == idx && !val) return true;
					model.set_property(idx, val);
				});
			});
		}
	});

	/**
	 * (???)
	 * Unreviewed code...
	 */
	var Popup_Fields_Complex_BooleanField = Backbone.View.extend({
		className: "upfront_popup-fields-complex_boolean clearfix",
		initialize: function (opts) {
			this.options = opts;
			var model = opts.model,
				boolean_values = opts.boolean_field.values || []
			;
			if (!boolean_values.length) {
				boolean_values.push({label: "", value: "1"});
			}

			this.options.field = new Upfront.Views.Editor.Field.Radios(_.extend(
				opts.boolean_field, {
					model: model,
					mutiple: false,
					values: boolean_values
			}));
		},
		render: function () {
			this.$el.empty();

			this.options.subfield.render();
			this.options.field.render();

			this.$el.append(this.options.field.$el);
			this.$el.append(this.options.subfield.$el);

			if (this.options.additional_class) this.$el.addClass(this.options.additional_class);
		},
		get_value: function () {
			var data = {};
			data[this.options.field.get_name()] = this.options.field.get_value();
			data[this.options.subfield.get_name()] = this.options.subfield.get_value();
			return data;
		}
	});

	/**
	 * (???)
	 * Unreviewed code...
	 */
	var PopupSettings = Upfront.Views.Editor.Settings.Settings.extend({
		initialize: function (opts) {
			this.has_tabs = false;
			this.options = opts;
			var panel = new PopupSettings_Panel({model: this.model});
			this.panels = _([
				panel
			]);
		},
		get_title: function () {
			return l10n.settings;
		}
	});

	/**
	 * (Settings Panel)
	 * Unreviewed code
	 */
	var PopupSettings_Panel = Upfront.Views.Editor.Settings.Panel.extend({
		initialize: function (opts) {
			this.options = opts;
			var appearance = new PopupSettings_Field_DisplayAppearance({model: this.model}),
				behavior = new PopupSettings_Field_DisplayBehavior({model: this.model}),
				trigger = new PopupSettings_Field_DisplayTrigger({model: this.model}),
				me = this
			;
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
			appearance.on("popup:appearance:changed", behavior.update, behavior);
			appearance.on("popup:appearance:changed", trigger.update, trigger);
			appearance.on("popup:appearance:changed", function () {
				me.trigger("upfront:settings:panel:refresh", me);
			})
		},
		render: function () {
			Upfront.Views.Editor.Settings.Panel.prototype.render.call(this);
			this.$el.addClass("upfront_popup-settings-panel");
			this.settings.each(function (setting) {
				if (setting.update) setting.update();
			});
		},
		get_label: function () {
			return l10n.display;
		},
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
				{"click": "register_change"}
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

		render: function () {
			this.$el.addClass( 'upfront-icon-element upfront-icon-element-popup' );
			this.$el.html( l10n.element_name );
		},

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
	 * @todo: What exactly does this do?
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
			//cssSelectorsId: Upfront.data.upfront_popup.defaults.type
		}
	);

	/*
	 * @todo: Why add the Model + View twice? here and in add_object()...
	 */
	Upfront.Models.PopupModel = PopupModel;
	Upfront.Views.PopupView = PopupView;

});
})(jQuery);