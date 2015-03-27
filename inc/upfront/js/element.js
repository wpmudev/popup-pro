(function() {
define(
[
	_popup_uf_data.base_url + 'js/element-settings',
],
/**
 * Contains the logic for the Upfront Editor.
 *
 * @since  4.8.0.0
 *
 * @param  string editor_style
 * @param  string public_style
 */
function( PopupSettings ) {

	/**
	 * Define the translations
	 */
	var l10n = Upfront.Settings.l10n.popup_element;

	/**
	 * Define the Popup Model.
	 */
	var PopupModel = Upfront.Models.ObjectModel.extend({

		// ========== Init
		init: function() {
			/*
			 * All static properties are defined in php class Upfront_PopupView
			 * and are accessed here via `Upfront.data.upfront_popup.defaults`
			 */

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

					// Add additional HTML markup for the editor.
					add_edit_fields( me.$el );
				}

				// Add a few inline-editor fields to the PopUp preview.
				function add_edit_fields( el ) {
					var edit_title, edit_subtitle,
						el_title, el_subtitle,
						edit_wrap = '<div class="uf-inline-edit"></div>',
						value_wrap = '<div class="uf-inline-value"></div>';

					el_title =  el.find( '.wdpu-title' );
					el_subtitle =  el.find( '.wdpu-subtitle' );

					edit_title = jQuery( '<input type="text">' )
						.attr( 'placeholder', l10n.title )
						.attr( 'name', 'title' )
						.change( change_inline_value )
						.val( jQuery.trim( el_title.text() ) );
					edit_subtitle = jQuery( '<input type="text">' )
						.attr( 'placeholder', l10n.subtitle )
						.attr( 'name', 'subtitle' )
						.change( change_inline_value )
						.val( jQuery.trim( el_subtitle.text() ) );

					el_title.wrapInner( value_wrap );
					el_subtitle.wrapInner( value_wrap );

					edit_title.appendTo( el_title ).wrap( edit_wrap );
					edit_subtitle.appendTo( el_subtitle ).wrap( edit_wrap );
/*
var new_region = new Upfront.Models.Region(
	_.extend(
		_.clone( Upfront.data.region_default_args ),
		{
			"name": 'popup_test',
			"container": 'body',
			"title": 'Demo Region'
		}
	)
);
*/

				}

				// When an inline field was modified we update the property.
				function change_inline_value( ev ) {
					var inp = jQuery( this ),
						field = inp.attr( 'name' ),
						value = inp.val();

					me.model.set_property( 'popup__' + field, value, false );
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

		// ========== Get_content_markup
		get_content_markup: function() {
			var data, rendered,
				code = this.model.get_content(),
				element
			;

			// Fix tagless content causes WSOD
			try {
				element = jQuery( code );
			} catch ( error ) {
				element = jQuery( '<p>' + code + '</p>' );
			}

			if ( element.hasClass( 'plaintxt_padding' ) ) {
				code = element.html();
			}

			data = {
				'content': code
			};

			rendered = _.template( template, data );

			if ( ! this.is_edited() || jQuery.trim( content ) == '' ) {
				rendered += '<div class="upfront-quick-swap"><p>' + l10n.dbl_click + '</p></div>';
			}

			return rendered;
		},

		// ========== Is_edited
		is_edited: function() {
			var is_edited = this.model.get_property_value_by_name( 'is_edited' );
			return is_edited ? true : false;
		},

		// ========== On_render
		on_render: function() {
			var me = this,
				ueditor_config = {
					linebreaks: false,
					inserts: {},
					autostart: false
				}
			;

			function ueditor_start() {
				var $swap = jQuery(this).find('.upfront-quick-swap');
				if ( $swap.length ) {
					$swap.remove();
				}
				me.model.set_property('is_edited', true, true);
				Upfront.Events.trigger('upfront:element:edit:start', 'text');
			}

			function ueditor_stop() {
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

			function ueditor_sync() {
				//return;
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
		get_content_markup: function() {
			return !! this.markup ? this.markup : l10n.hold_on;
		}
	});

	/**
	 * Simply a collection of fields.
	 * This is the base-class of other objects (see below)
	 */
	var Popup_SettingsItem_ComplexItem = Upfront.Views.Editor.Settings.Item.extend({
		// ========== Save_fields
		save_fields: function() {
			var model = this.model;

			function save_field( field ) {
				var data = field.get_value();
				if ( !_.isObject( data ) ) { return; }

				_( data ).each( save_field_val );
			}

			function save_field_val( val, idx ) {
				//if ( 'appearance' == idx && ! val ) { return true; }
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
		initialize: function( opts ) {
			var panel;

			panel = new PopupSettings_Panel({model: this.model});

			this.has_tabs = true;
			this.options = opts;
			this.panels = _([
				panel
			]);
		},

		// ========== Get_title
		get_title: function() {
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
		initialize: function(opts) {
			var attr, appearance, cta,
				me = this
			;

			this.options = opts;
			attr = {model: this.model};

			appearance = new PopupSettings_Field_PanelAppearance( attr );
			cta = new PopupSettings_Field_PanelCta( attr );

			this.settings = _([
				appearance,
				cta
			]);

			appearance.on( 'popup:appearance:changed', cta.update, cta );
			appearance.on( 'popup:appearance:changed', function() {
				me.trigger( 'upfront:settings:panel:refresh', me );
			})
		},

		// ========== Render
		render: function() {
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
		get_label: function() {
			return l10n.settings;
		},

		// ========== Get_title
		get_title: function() {
			return l10n.settings;
		}
	});

	/**
	 * Appearance options of the PopUp.
	 */
	var PopupSettings_Field_PanelAppearance = Popup_SettingsItem_ComplexItem.extend({
		className: 'upfront_popup-item-appearance',

		// ========== Initialize
		initialize: function() {
			var key, style,
				me = this,
				styles = []
			;

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
				me.register_change(me);
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

		// ========== Render
		render: function() {
			Upfront.Views.Editor.Settings.Item.prototype.render.call(this);
			this.$el.find('.upfront-settings-item-content').addClass('clearfix');
		},

		// ========== Get_title
		get_title: function() {
			return l10n.panel_appearance;
		},

		// ========== Register_change
		register_change: function() {
			this.fields.each(function(field) {
				field.property.set({'value': field.get_value()}, {'silent': false});
			});
			this.trigger('popup:appearance:changed');
		}
	});

	/**
	 * Call To Action settings.
	 */
	var PopupSettings_Field_PanelCta = Popup_SettingsItem_ComplexItem.extend({
		className: 'upfront_popup-item-cta',

		// ========== Initialize
		initialize: function() {
			var me = this;

			function did_change() {
				me.register_change(me);
			}

			this.fields = _([
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__cta_label',
					label: l10n.cta_label,
					change: did_change
				}),
				new Upfront.Views.Editor.Field.Text({
					model: this.model,
					property: 'popup__cta_link',
					label: l10n.cta_link,
					change: did_change
				}),
			]);
		},

		// ========== Render
		render: function() {
			Upfront.Views.Editor.Settings.Item.prototype.render.call(this);
			this.$el.find('.upfront-settings-item-content').addClass('clearfix');
		},

		// ========== Get_title
		get_title: function() {
			return l10n.panel_cta;
		},

		// ========== Register_change
		register_change: function() {
			this.fields.each(function(field) {
				field.property.set({'value': field.get_value()}, {'silent': false});
			});
			this.trigger('popup:cta:changed');
		}
	});

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
	 * Defines custom Context-Menu-Items to manually switch to "Edit Text" mode.
	 */
	var PopupMenuList = Upfront.Views.ContextMenuList.extend({
		// ========== Initialize
		initialize: function() {
			var me = this,
				mnu_editmode;

			// Define the menu item "Edit Text"
			mnu_editmode = new Upfront.Views.ContextMenuItem({
				// Menu item label.
				get_label: function() {
					return l10n.edit_text;
				},

				// Action handler, on click.
				action: function() {
					var container = me.for_view.$el.find( 'div.upfront-object-content' ),
						editor = container.data( 'ueditor' );

					// Start editor mode, when not started yet.
					if ( ! container.data( 'redactor' ) ) {
						editor.start();

						// Stop editor mode when user clicks outside the editor.
						jQuery( document ).on('click', function( ev ){
							if ( ! editor.options.autostart && editor.redactor ) {
								var $target = jQuery( ev.target );

								if ( ! editor.disableStop &&
									! $target.closest( 'li' ).length &&
									! $target.closest( '.redactor_air' ).length &&
									! $target.closest( '.ueditable' ).length
								) {
									editor.stop();
								}
							}
						});
					}
				}
			});

			this.menuitems = _([ mnu_editmode ]);
		}
	});

	/**
	 * Defines the full Context-Menu when right-clicking on the PopUp container.
	 * This menu effectively adds our custom menu-items that we defined above.
	 */
	var PopupMenu = Upfront.Views.ContextMenu.extend({
		initialize: function() {
			this.menulists = _([
				new PopupMenuList()
			]);
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

			// Modifications to the right-click context menu.
			'ContextMenu': PopupMenu,

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