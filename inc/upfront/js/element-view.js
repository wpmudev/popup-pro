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

	// Return the module object.
	return PopupView;

});
})();