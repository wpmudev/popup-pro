/**
 * Admin Javascript functions for Pop Up
 */

jQuery(function init_admin() {

	// ----- POPUP EDITOR --

	// Disables dragging of metaboxes: Users cannot change the metabox order.
	function disable_metabox_dragging() {
		var boxes = jQuery( '.meta-box-sortables' ),
			handles = jQuery( '.postbox .hndle' );

		if ( ! boxes.length ) { return; }

		boxes.sortable({
			disabled: true
		});
		handles.css( 'cursor', 'pointer' );
	}

	// Keeps the submitdiv always visible, even when scrolling.
	function scrolling_submitdiv() {
		var top_offset,
			padding = 20,
			submitdiv = jQuery( '#submitdiv' ),
			postbody = jQuery( '#post-body' ),
			body = jQuery( 'body' ),
			padding = 20;

		if ( ! submitdiv.length ) { return; }
		top_offset = submitdiv.position().top;

		jQuery( window ).scroll(function(){
			if ( postbody.hasClass( 'columns-1' ) ) {
				// 1-column view:
				// The div stays as sticky toolbar when scrolling down.

				var scroll_top = jQuery( window ).scrollTop() - top_offset - 36;
						// 36 is the height of the submitdiv title

				if ( scroll_top > 0 ) {
					if ( ! body.hasClass( 'sticky-submit' ) ) {
						body.addClass( 'sticky-submit' );
						submitdiv.css({ 'marginTop': 0 } );
						submitdiv.find( '.sticky-actions' ).show();
						submitdiv.find( '.non-sticky' ).hide();
					}
				} else {
					if ( body.hasClass( 'sticky-submit' ) ) {
						body.removeClass( 'sticky-submit' );
						submitdiv.find( '.sticky-actions' ).hide();
						submitdiv.find( '.non-sticky' ).show();
					}
				}
			} else {
				// 2-column view:
				// The div scrolls with the page to stay visible.

				var scroll_top = jQuery( window ).scrollTop() - top_offset + padding;

				if ( scroll_top > 0 ) {
					submitdiv.css({ 'marginTop': scroll_top } );
				} else {
					submitdiv.css({ 'marginTop': 0 } );
				}
			}
		});
	}

	// Change the text-fields to colorpicker fields.
	function init_colorpicker() {
		var inp = jQuery( '.colorpicker' );

		if ( ! inp.length || 'function' != typeof inp.wpColorPicker ) { return; }

		var maybe_hide_picker = function maybe_hide_picker( ev ) {
			var el = jQuery( ev.target ),
				cp = el.closest( '.wp-picker-container' ),
				me = cp.find( '.colorpicker' ),
				do_hide = jQuery( '.colorpicker' );

			if ( cp.length ) {
				do_hide = do_hide.not( me );
			}

			do_hide.each( function() {
				var picker = jQuery( this ),
					wrap = picker.closest( '.wp-picker-container' );

				picker.iris( 'hide' );

				// As mentioned: Color picker does not like to hide properly...
				picker.hide();
				wrap.find( '.wp-picker-clear').addClass( 'hidden' );
				wrap.find( '.wp-picker-open').removeClass( 'wp-picker-open' );
			});
		};

		inp.wpColorPicker();

		// Don't ask why the handler is hooked three times ;-)
		// The Color picker is a bit bitchy when it comes to hiding it...
		jQuery( document ).on( 'mousedown', maybe_hide_picker );
		jQuery( document ).on( 'click', maybe_hide_picker );
		jQuery( document ).on( 'mouseup', maybe_hide_picker );
	}

	// Add event handlers for editor UI controls (i.e. to checkboxes)
	function init_edit_controls() {
		var chk_colors = jQuery( '#po-custom-colors' ),
			chk_size = jQuery( '#po-custom-size' ),
			opt_disp_delay = jQuery( '#po-display-delay' ),
			opt_disp_scroll = jQuery( '#po-display-scroll' ),
			opt_disp_anchor = jQuery( '#po-display-anchor' ),
			chk_can_hide = jQuery( '#po-can-hide' ),
			chk_close_hides = jQuery( '#po-close-hides' );

		if ( ! chk_colors.length ) { return; }

		var toggle_section = function toggle_section() {
			var me = jQuery( this ),
				sel = me.data( 'toggle' ),
				sect = jQuery( sel ),
				group_or = me.data( 'or' ),
				group_and = me.data( 'and' ),
				is_active = false;

			if ( ! sect.length ) { return; }

			if ( group_or ) {
				var group = jQuery( group_or );
				is_active = ( group.filter( ':checked' ).length > 0);
			} else if ( group_and ) {
				var group = jQuery( group_and );
				is_active = ( group.length == group.filter( ':checked' ).length );
			} else {
				is_active = me.prop( 'checked' );
			}

			if ( is_active ) {
				sect.removeClass( 'inactive' );
				sect.find( 'input,select,textarea,a' )
					.prop( 'readonly', false )
					.removeClass( 'disabled' );
			} else {
				sect.addClass( 'inactive' );
				// Don't set .prop('disabled', true)!
				sect.find( 'input,select,textarea,a' )
					.prop( 'readonly', true )
					.addClass( 'disabled' );
			}
			sect.addClass( 'inactive-anim' );
		};

		var toggle_section_group = function toggle_section_group() {
			var me = jQuery( this ),
				name = me.attr( 'name' ),
				group = jQuery( '[name="' + name + '"]' );

			group.each(function() {
				toggle_section.call( this );
			});
		}

		chk_colors.click( toggle_section );
		chk_size.click( toggle_section );
		chk_can_hide.click( toggle_section );
		chk_close_hides.click( toggle_section );
		opt_disp_delay.click( toggle_section_group );
		opt_disp_scroll.click( toggle_section_group );
		opt_disp_anchor.click( toggle_section_group );

		toggle_section.call( chk_colors );
		toggle_section.call( chk_size );
		toggle_section.call( chk_can_hide );
		toggle_section.call( chk_close_hides );
		toggle_section.call( opt_disp_delay );
		toggle_section.call( opt_disp_scroll );
		toggle_section.call( opt_disp_anchor );
	}

	// ----- POPUP LIST --

	// Adds custom bulk actions to the popup list.
	function bulk_actions() {
		var key,
			ba1 = jQuery( 'select[name="action"] '),
			ba2 = jQuery( 'select[name="action2"] ');

		if ( ! ba1.length || 'object' != typeof window.po_bulk ) { return; }

		for ( key in po_bulk ) {
			jQuery( '<option>' )
				.val( key )
				.text( po_bulk[key] )
				.appendTo( ba1 )
				.clone()
				.appendTo( ba2 );
		}
	}

	// Makes the post-list sortable (to change popup-order)
	function sortable_list() {
		var table = jQuery( 'table.posts' );
			tbody = table.find( '#the-list' );

		if ( ! tbody.length ) { return; }

		var ajax_done = function ajax_done( resp, okay ) {
			table.removeClass( 'wpmui-loading' );
			if ( okay ) {
				for ( var id in resp ) {
					if ( ! resp.hasOwnProperty( id ) ) { continue; }
					tbody.find( '#post-' + id + ' .the-pos' ).text( resp[id] );
				}
			}
		};

		var save_order = function save_order( event, ui ) {
			var i,
				rows = tbody.find('tr'),
				order = [];

			for ( i = 0; i < rows.length; i+= 1 ) {
				order.push( jQuery( rows[i] ).attr( 'id' ) );
			}

			table.addClass( 'wpmui-loading' );
			wpmUi.ajax( null, 'po-ajax' )
				.data({
					'do': 'order',
					'order': order
				})
				.ondone( ajax_done )
				.load_json();
		};

		tbody.sortable({
			placeholder: 'ui-sortable-placeholder',
			axis: 'y',
			handle: '.column-po_order',
			helper: 'clone',
			opacity: .75,
			update: save_order
		});
		tbody.disableSelection();
	}

	if ( ! jQuery( 'body.post-type-inc_popup' ).length ) {
		return;
	}

	// EDITOR
	if ( jQuery( 'body.post-php' ).length ) {
		disable_metabox_dragging();
		scrolling_submitdiv();
		init_colorpicker();
		init_edit_controls();
	}

	// POPUP LIST
	else if ( jQuery( 'body.edit-php' ).length ) {
		sortable_list();
		bulk_actions();
	}

});