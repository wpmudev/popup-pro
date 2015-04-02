(function () {
define(
[
	_popup_uf_data.base_url + 'js/element-model.js',
],
/**
 * The view object - this is the preview displayed in the Upfront editor.
 *
 * @since  4.8.0.0
 */
function( PopupModel ) {

	/**
	 * Define the translations
	 */
	var l10n = Upfront.Settings.l10n.popup_element;

	/**
	 * Define the Popup View that is displayed as inline block in edit mode.
	 */
	var PopupView = Upfront.Views.ObjectView.extend({

		/*
		 * This holds the preview HTML markup.
		 * Markup will be re-created when this property is empty.
		 *
		 * @var string
		 */
		markup: null,

		// ========== Initialize
		initialize: function initialize() {
			var me = this;

			function property_changed( model ) {
				if ( ! model || ! model.get) { return true; }

				if ( 'row' !== model.get( 'name' ) ) {
					me.markup = null;
					me.render();
				}
			}

			if ( ! ( this.model instanceof PopupModel ) ) {
				this.model = new PopupModel({
					properties: this.model.get( 'properties' )
				});
			}

			Upfront.Views.ObjectView.prototype.initialize.call( this );

			Upfront.Events.on( 'preview:build:stop', function() {
				me.update_dom.apply( me );
			});
			Upfront.Events.on( 'entity:object:after_render', function( view, model ) {
				if ( view !== me ) { return; }
				me.update_dom.apply( me );
			});

			this.model.get( 'properties' ).on( 'change', property_changed );
		},

		// ========== Render
		render: function render() {
			if ( ! this.markup ) {
				var me = this,
					options = Upfront.Util.model_to_json( this.model ),
					data = {};

				// Display the Popup preview that we got from the Server object.
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

		// ========== Update_dom
		update_dom: function update_dom() {
			var me = this;

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
			}

			// Add an Edit-Button that opens the contents lightbox of the popup.
			function add_content_editor( el ) {
				var button, parent;

				// Remove old hover action elements.
				el.find( '.uf-hover-action' ).remove();

				// Find the correct parent element to append the hover actions to.
				parent = el.find( '.wdpu-buttons' );
				if ( ! parent.length ) {
					parent = el.find( '.wdpu-text' );
				}
				parent = parent.parent();

				// Add the button to the preview.
				button = jQuery( '<button type="button"></button>' )
					.text( l10n.edit_text )
					.click( edit_contents )
					.appendTo( parent )
					.wrap( '<div class="uf-hover-action"></div>' );
			}

			// When an inline field was modified we update the property.
			function change_inline_value( ev ) {
				var inp = jQuery( this ),
					field = inp.attr( 'name' ),
					value = inp.val();

				me.model.set_property( 'popup__' + field, value, false );
			}

			// Show the Popup contents region.
			function edit_contents() {
				var region_id,
					region_name = 'PopUp Contents';

				region_id = me.model.get_property_value_by_name( 'content_region' );

				if ( region_id ) {
					// If we created the Content-Region already then open it now.
					Upfront.Application.LayoutEditor.openLightboxRegion( region_id );
				} else {
					// If we do not have a Content-Region for this Popup then create one.
					region_id = Upfront.Application.LayoutEditor.createLightboxRegion( region_name );
					me.model.set_property( 'content_region', region_id );
					me.model.set({ url: '#' + region_id });
				}

				me.render();
			}

			// Add additional HTML markup for the editor.
			add_edit_fields( me.$el );

			// Add an Edit-Button to edit the popup contents.
			add_content_editor( me.$el );
		},

		// ========== Is_edited
		is_edited: function is_edited() {
			var is_edited = this.model.get_property_value_by_name( 'is_edited' );
			return is_edited ? true : false;
		},

		// ========== On_render
		on_render: function on_render() {
			var me = this;

			this.$el.find( '.upfront-object-content' )
				.addClass( 'upfront-plain_txt' );
		},

		// ========== Get_content_markup
		get_content_markup: function get_content_markup() {
			return !! this.markup ? this.markup : l10n.hold_on;
		}

	});

	// Return the module object.
	return PopupView;

});
})();