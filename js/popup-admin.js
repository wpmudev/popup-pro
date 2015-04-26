/*! PopUp Pro - v4.7.07
 * http://premium.wpmudev.org/project/the-pop-over-plugin/
 * Copyright (c) 2015; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global wp:false */
/*global wpmUi:false */
/*global ace:false */

/**
 * Admin Javascript functions for PopUp
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
		var scroll_top,
			top_offset,
			submitdiv = jQuery( '#submitdiv' ),
			postbody = jQuery( '#post-body' ),
			body = jQuery( 'body' ),
			padding = 20;

		if ( ! submitdiv.length ) { return; }
		top_offset = submitdiv.position().top;

		var small_make_sticky = function() {
			if ( ! body.hasClass( 'sticky-submit' ) ) {
				body.addClass( 'sticky-submit' );
				submitdiv.css({ 'marginTop': 0 } );
				submitdiv.find( '.sticky-actions' ).show();
				submitdiv.find( '.non-sticky' ).hide();
			}
		};

		var small_remove_sticky = function() {
			if ( body.hasClass( 'sticky-submit' ) ) {
				body.removeClass( 'sticky-submit' );
				submitdiv.find( '.sticky-actions' ).hide();
				submitdiv.find( '.non-sticky' ).show();
			}
		};

		jQuery( window ).resize(function() {
			var is_small = jQuery( window ).width() <= 850;

			if ( is_small ) {
				if ( ! body.hasClass( 'po-small' ) ) {
					body.addClass( 'po-small' );
				}
			} else {
				if ( body.hasClass( 'po-small' ) ) {
					body.removeClass( 'po-small' );
					small_remove_sticky();
				}
			}
		}).scroll(function(){
			if ( postbody.hasClass( 'columns-1' ) || body.hasClass( 'po-small' ) ) {
				// 1-column view:
				// The div stays as sticky toolbar when scrolling down.

				scroll_top = jQuery( window ).scrollTop() - top_offset;

				if ( scroll_top > 0 ) {
					small_make_sticky();
				} else {
					small_remove_sticky();
				}
			} else {
				// 2-column view:
				// The div scrolls with the page to stay visible.

				scroll_top = jQuery( window ).scrollTop() - top_offset + padding;

				if ( scroll_top > 0 ) {
					submitdiv.css({ 'marginTop': scroll_top } );
				} else {
					submitdiv.css({ 'marginTop': 0 } );
				}
			}
		});

		window.setTimeout( function() {
			jQuery( window ).trigger( 'scroll' );
		}, 100 );
	}

	// Change the text-fields to colorpicker fields.
	function init_colorpicker() {
		var inp = jQuery( '.colorpicker' );

		if ( ! inp.length || 'function' !== typeof inp.wpColorPicker ) { return; }

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
			opt_display = jQuery( '[name=po_display]' ),
			chk_can_hide = jQuery( '#po-can-hide' ),
			chk_close_hides = jQuery( '#po-close-hides' );

		if ( ! chk_colors.length ) { return; }

		var toggle_section = function toggle_section() {
			var group,
				me = jQuery( this ),
				sel = me.data( 'toggle' ),
				sect = jQuery( sel ),
				group_or = me.data( 'or' ),
				group_and = me.data( 'and' ),
				is_active = false;

			if ( group_or ) {
				group = jQuery( group_or );
				is_active = ( group.filter( ':checked' ).length > 0);
			} else if ( group_and ) {
				group = jQuery( group_and );
				is_active = ( group.length === group.filter( ':checked' ).length );
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
				// Do NOT set .prop('disabled', true)!
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
		};

		var create_sliders = function create_sliders() {
			jQuery( '.slider' ).each(function() {
				var me = jQuery( this ),
					wrap = me.closest( '.slider-wrap' ),
					inp_base = me.data( 'input' ),
					inp_min = wrap.find( inp_base + 'min' ),
					inp_max = wrap.find( inp_base + 'max' ),
					min_input = wrap.find( '.slider-min-input' ),
					min_ignore = wrap.find( '.slider-min-ignore' ),
					max_input = wrap.find( '.slider-max-input' ),
					max_ignore = wrap.find( '.slider-max-ignore' ),
					min = me.data( 'min' ),
					max = me.data( 'max' );

				if ( isNaN( min ) ) { min = 0; }
				if ( isNaN( max ) ) { max = 9999; }
				inp_min.prop( 'readonly', true );
				inp_max.prop( 'readonly', true );

				var update_fields = function update_fields( val1, val2 ) {
					inp_min.val( val1 );
					inp_max.val( val2 );

					if ( val1 === min ) {
						min_input.hide();
						min_ignore.show();
					} else {
						min_input.show();
						min_ignore.hide();
					}
					if ( val2 === max ) {
						max_input.hide();
						max_ignore.show();
					} else {
						max_input.show();
						max_ignore.hide();
					}
				};

				me.slider({
					range: true,
					min: min,
					max: max,
					values: [ inp_min.val(), inp_max.val() ],
					slide: function( event, ui ) {
						update_fields( ui.values[0], ui.values[1] );
					}
				});

				update_fields( inp_min.val(), inp_max.val() );
			});
		};

		chk_colors.click( toggle_section );
		chk_size.click( toggle_section );
		chk_can_hide.click( toggle_section );
		chk_close_hides.click( toggle_section );
		opt_display.click( toggle_section_group );

		toggle_section.call( chk_colors );
		toggle_section.call( chk_size );
		toggle_section.call( chk_can_hide );
		toggle_section.call( chk_close_hides );

		opt_display.each(function() {
			toggle_section.call( jQuery( this ) );
		});

		create_sliders();
	}

	// Toggle rules on/off
	function init_rules() {
		var all_rules = jQuery( '#meta-rules .all-rules' ),
			active_rules = jQuery( '#meta-rules .active-rules' );

		if ( ! all_rules.length ) { return; }

		var toggle_checkbox = function toggle_checkbox( ev ) {
			var me = jQuery( ev.target ),
				chk = me.find( 'input.wpmui-toggle-checkbox' );

			if ( me.closest( '.wpmui-toggle' ).length ) { return; }
			if ( me.hasClass( 'inactive' ) ) { return false; }
			chk.trigger( 'click' );
		};

		var toggle_rule = function toggle_rule() {
			var me = jQuery( this ),
				rule = me.closest( '.rule' ),
				sel = me.data( 'form' ),
				form = active_rules.find( sel ),
				active = me.prop( 'checked' );

			if ( active ) {
				rule.removeClass( 'off' ).addClass( 'on' );
				form.removeClass( 'off' ).addClass( 'on open' );
			} else {
				rule.removeClass( 'on' ).addClass( 'off' );
				form.removeClass( 'on' ).addClass( 'off' );
			}

			exclude_rules( me, active );
		};

		var exclude_rules = function exclude_rules( checkbox, active ) {
			var ind, excl1, excl2,
				excl = checkbox.data( 'exclude' ),
				keys = (excl ? excl.split( ',' ) : []);

			// Exclude other rules.
			for ( ind = keys.length - 1; ind >= 0; ind -= 1 ) {
				excl1 = all_rules.find( '.rule-' + keys[ ind ] );
				excl2 = active_rules.find( '#po-rule-' + keys[ ind ] );

				if ( excl1.hasClass( 'on' ) ) {
					// Rule is active; possibly migrated from old PopUp editor
					// so we cannot disable the rule now...
					continue;
				}

				excl1.prop( 'disabled', active );
				if ( active ) {
					excl1.addClass( 'inactive off' ).removeClass( 'on' );
					excl2.addClass( 'off' ).removeClass( 'on' );
				} else {
					excl1.removeClass( 'inactive off' );
				}
			}
		};

		var toggle_form = function toggle_form() {
			var me = jQuery( this ),
				form = me.closest( '.rule' );

			form.toggleClass( 'open' );
		};

		all_rules.find( 'input.wpmui-toggle-checkbox' ).click( toggle_rule );
		all_rules.find( '.rule' ).click( toggle_checkbox );
		active_rules.on( 'click', '.rule-title,.rule-toggle', toggle_form );

		// Exclude rules.
		all_rules.find( '.rule.on input.wpmui-toggle-checkbox' ).each(function() {
			exclude_rules( jQuery( this ), true );
		});
		jQuery( '.init-loading' ).removeClass( 'wpmui-loading' );
	}

	// Hook up the "Featured image" button.
	function init_image() {
		// Uploading files
		var box = jQuery( '.content-image' ),
			btn = box.find( '.add_image' ),
			dropzone = box.find( '.featured-img' ),
			reset = box.find( '.reset' ),
			inp = box.find( '.po-image' ),
			img_preview = box.find( '.img-preview' ),
			img_label = box.find( '.lbl-empty' ),
			img_pos = box.find( '.img-pos' ),
			file_frame;

		// User selected an image (via drag-drop or file_frame)
		var use_image = function use_image( url ) {
			inp.val( url );
			img_preview.attr( 'src', url ).show();
			img_label.hide();
			img_pos.show();
			dropzone.addClass( 'has-image' );
		};

		// User selected an image (via drag-drop or file_frame)
		var reset_image = function reset_image( url ) {
			inp.val( '' );
			img_preview.attr( 'src', '' ).hide();
			img_label.show();
			img_pos.hide();
			dropzone.removeClass( 'has-image' );
		};

		// User clicks on the "Add image" button.
		var select_clicked = function select_clicked( ev ) {
			ev.preventDefault();

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: btn.attr( 'data-title' ),
				button: {
					text: btn.attr( 'data-button' )
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				// We set multiple to false so only get one image from the uploader
				var attachment = file_frame.state().get('selection').first().toJSON();

				// Do something with attachment.id and/or attachment.url here
				use_image( attachment.url );
			});

			// Finally, open the modal
			file_frame.open();
		};

		var select_pos = function select_pos( ev ) {
			var me = jQuery( this );

			img_pos.find( '.option' ).removeClass( 'selected' );
			me.addClass( 'selected' );
		};

		btn.on( 'click', select_clicked );
		reset.on( 'click', reset_image );
		img_pos.on( 'click', '.option', select_pos );
	}

	// ----- POPUP LIST --

	// Adds custom bulk actions to the popup list.
	function bulk_actions() {
		var key,
			ba1 = jQuery( 'select[name="action"] '),
			ba2 = jQuery( 'select[name="action2"] ');

		if ( ! ba1.length || 'object' !== typeof window.po_bulk ) { return; }

		for ( key in window.po_bulk ) {
			jQuery( '<option>' )
				.val( key )
				.text( window.po_bulk[key] )
				.appendTo( ba1 )
				.clone()
				.appendTo( ba2 );
		}
	}

	// Makes the post-list sortable (to change popup-order)
	function sortable_list() {
		var table = jQuery( 'table.posts' ),
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
			opacity: 0.75,
			update: save_order
		});
		tbody.disableSelection();
	}

	// Shows a preview of the current PopUp.
	function init_preview() {
		var doc = jQuery( document ),
			body = jQuery( '#wpcontent' );

		var handle_list_click = function handle_list_click( ev ) {
			var me = jQuery( this ),
				po_id = me.data( 'id' );

			ev.preventDefault();
			if ( undefined === window.inc_popup ) { return false; }

			body.addClass( 'wpmui-loading' );
			window.inc_popup.load( po_id );
			return false;
		};

		var handle_editor_click = function handle_editor_click( ev ) {
			var data,
				me = jQuery( this ),
				form = jQuery( '#post' ),
				ajax = wpmUi.ajax();

			ev.preventDefault();
			if ( undefined === window.inc_popup ) { return false; }

			data = ajax.extract_data( form );
			body.addClass( 'wpmui-loading' );
			window.inc_popup.load( 0, data );
			return false;
		};

		var show_popup = function show_popup( ev, popup ) {
			body.removeClass( 'wpmui-loading' );
			popup.init();
		};

		doc.on( 'click', '.posts .po-preview', handle_list_click );
		doc.on( 'click', '#post .preview', handle_editor_click );
		doc.on( 'popup-initialized', show_popup );
	}

	// Initialize the CSS editor
	function init_css_editor() {
		jQuery('.po_css_editor').each(function(){
			var editor = ace.edit(this.id);

			jQuery(this).data('editor', editor);
			editor.setTheme('ace/theme/chrome');
			editor.getSession().setMode('ace/mode/css');
			editor.getSession().setUseWrapMode(true);
			editor.getSession().setUseWrapMode(false);
		});

		jQuery('.po_css_editor').each(function(){
			var self = this,
				input = jQuery( jQuery(this).data('input') );

			jQuery(this).data('editor').getSession().on('change', function () {
				input.val( jQuery(self).data('editor').getSession().getValue() );
			});
		});
	}

	if ( ! jQuery( 'body.post-type-inc_popup' ).length ) {
		return;
	}

	// EDITOR
	if ( jQuery( 'body.post-php' ).length || jQuery( 'body.post-new-php' ).length ) {
		disable_metabox_dragging();
		scrolling_submitdiv();
		init_colorpicker();
		init_edit_controls();
		init_rules();
		init_preview();
		init_image();
		init_css_editor();

		wpmUi.upgrade_multiselect();
	}

	// POPUP LIST
	else if ( jQuery( 'body.edit-php' ).length ) {
		sortable_list();
		bulk_actions();
		init_preview();
	}

});