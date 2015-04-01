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
		initialize: function() {
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

			this.model.get( 'properties' ).on( 'change', property_changed );
			window.po_view = me; // DEBUG ONLY
		},

		// ========== Render
		render: function() {
			if ( ! this.markup ) {
				var me = this,
					options = Upfront.Util.model_to_json( this.model ),
					data = {};

				// Display the Popup preview that we got from the Server object.
				function markup_loaded( response ) {
					me.markup = response.data;
					Upfront.Views.ObjectView.prototype.render.call( me );

					// Add additional HTML markup for the editor.
					add_edit_fields( me.$el );

					// Add a drop-region inside the Popup to add Upfront elements.
					add_inner_region( me, 'Popup Contents' );
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

		// ========== Is_edited
		is_edited: function() {
			var is_edited = this.model.get_property_value_by_name( 'is_edited' );
			return is_edited ? true : false;
		},

		// ========== On_render
		on_render: function() {
			var me = this;

			this.$el.find( '.upfront-object-content' )
				.addClass( 'upfront-plain_txt' );
		},

		// ========== Get_content_markup
		get_content_markup: function() {
			return !! this.markup ? this.markup : l10n.hold_on;
		}

	});

	/**
	 * Add a new region to the view.
	 * Called by the Views render() method.
	 *
	 * @todo: This region is currently added above the popup. It should be inside .wdpu-content.
	 * @todo: Dropping elements onto this region adds them to the parent region instead of this region.
	 */
	function add_inner_region( the_view, region_title ) {
		var new_region,
			the_model = the_view.model,
			collection = the_model.collection,
			region_name = region_title.toLowerCase().replace( /\s/g, '-' ),
			region_args = {};

		// Set-up the initial options of the new region.
		region_args.name = region_name; // Internal region name.
		region_args.container = 'lightbox'; // Parent container (?? not sure what this refers to) @todo: review and correct!
		region_args.title = region_title; // Title displayed in the region-editor.
		region_args.type = 'lightbox'; // Types: lightbox|fixed|wide|(...)
		region_args.scope = 'global'; // Scope: global|local

		// Create the region object.
		new_region = new Upfront.Models.Region(
			_.extend(
				_.clone( Upfront.data.region_default_args ),
				region_args
			)
		);

		// Set some advanced properties of the new region.
		new_region.set_property( 'row', Upfront.Util.height_to_row( 300 ) );

		// Add our new region to the PopupModel collection.
		// @todo: I don't understand this part, but without this the region is not displayed at all...
		new_region.add_to( collection, 0, {} );

		/**
		 * Upfront.Application.layout_view.local_view
		 * This is the `Regions` view as defined in upfront-views.js
		 */
		// @todo: This seems to have no effect if left away. Not sure what it's used for or if needed...
		Upfront.Application.layout_view.local_view.create_region_instance( new_region );
	}

	// Return the module object.
	return PopupView;

});
})();