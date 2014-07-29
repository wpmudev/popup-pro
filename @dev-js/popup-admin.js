/**
 * Admin Javascript functions for Pop Up
 */

jQuery(function init_admin() {

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

	// Makes the post-list sortable (to change popup-order)
	function sortable_list() {
		var table = jQuery( 'table.posts' );
			tbody = table.find( '#the-list' );

		if ( ! tbody.length ) { return; }

		var ajax_done = function ajax_done( resp, okay ) {
			table.removeClass( 'wpmui-loading' );
			console.log ('Ajax done', okay, resp );
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

	if ( jQuery( 'body.post-php' ).length ) {
		disable_metabox_dragging();
		scrolling_submitdiv();
	}

	else if ( jQuery( 'body.edit-php' ).length ) {
		sortable_list();
	}

});