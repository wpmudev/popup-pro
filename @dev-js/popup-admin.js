/**
 * Admin Javascript functions for Pop Up
 */

jQuery(function init_admin() {

	// Disables dragging of metaboxes: Users cannot change the metabox order.
	function disable_metabox_dragging() {
		jQuery( '.meta-box-sortables' ).sortable({
			disabled: true
		});
		jQuery( '.postbox .hndle' ).css( 'cursor', 'pointer' );
	}

	// Keeps the submitdiv always visible, even when scrolling.
	function scrolling_submitdiv() {
		var submitdiv = jQuery( '#submitdiv' ),
			postbody = jQuery( '#post-body' ),
			body = jQuery( 'body' ),
			top_offset = submitdiv.position().top,
			padding = 20;

		jQuery( window ).scroll(function(){
			if ( postbody.hasClass( 'columns-1' ) ) {
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
				var scroll_top = jQuery( window ).scrollTop() - top_offset + padding;

				if ( scroll_top > 0 ) {
					submitdiv.css({ 'marginTop': scroll_top } );
				} else {
					submitdiv.css({ 'marginTop': 0 } );
				}
			}
		});
	}

	if ( jQuery( 'body.post-type-inc_popup' ).length ) {
		disable_metabox_dragging();
		scrolling_submitdiv();
	}

});