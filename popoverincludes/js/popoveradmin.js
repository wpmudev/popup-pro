function popoverremoveaction() {
	var section = jQuery(this).attr('id');
	var sectionname = section.replace('remove-','');

	jQuery('#main-' + sectionname).appendTo('#hiden-actions');
	jQuery('#' + sectionname).show();

	// Move from the fields
	jQuery('#in-positive-rules').val( jQuery('#in-positive-rules').val().replace(',' + sectionname, ''));

	return false;
}

function popoverremovemessage() {

	jQuery('#upmessage').remove();
	return false;
}

function popoverclickactiontoggle() {
	if(jQuery(this).parent().hasClass('open')) {
		jQuery(this).parent().removeClass('open').addClass('closed');
		jQuery(this).parents('.action').find('.action-body').removeClass('open').addClass('closed');
	} else {
		jQuery(this).parent().removeClass('closed').addClass('open');
		jQuery(this).parents('.action').find('.action-body').removeClass('closed').addClass('open');
	}
}

function popoveraddtorules() {

	moving = jQuery(this).parents('.popover-draggable').attr('id');

	if(moving != '') {
		jQuery('#main-' + moving).appendTo('#positive-rules-holder');
		jQuery('#' + moving).hide();

		// put the name in the relevant holding input field
		jQuery('#in-positive-rules').val( jQuery('#in-positive-rules').val() + ',' + moving );
	}

	return false;
}

function popoverReady() {

	jQuery('.popover-draggable').draggable({
			opacity: 0.7,
			helper: 'clone',
			start: function(event, ui) {
					jQuery('input#beingdragged').val( jQuery(this).attr('id') );
				 },
			stop: function(event, ui) {
					jQuery('input#beingdragged').val( '' );
				}
				});

	jQuery('.droppable-rules').droppable({
			hoverClass: 'hoveringover',
			drop: function(event, ui) {
					moving = jQuery('input#beingdragged').val();
					ruleplace = jQuery(this).attr('id');
					if(moving != '') {
						jQuery('#main-' + moving).appendTo('#' + ruleplace + '-holder');
						jQuery('#' + moving).hide();

						// put the name in the relevant holding input field
						jQuery('#in-' + ruleplace).val( jQuery('#in-' + ruleplace).val() + ',' + moving );
					}
				}
	});

	jQuery('#positive-rules-holder').sortable({
		opacity: 0.7,
		helper: 'clone',
		placeholder: 'placeholder-rules',
		update: function(event, ui) {
				jQuery('#in-positive-rules').val(',' + jQuery('#positive-rules-holder').sortable('toArray').join(',').replace(/main-/gi, ''));
			}
	});

	jQuery('a.removelink').click(popoverremoveaction);
	jQuery('a#closemessage').click(popoverremovemessage);

	jQuery('.action .action-top .action-button').click(popoverclickactiontoggle);

	jQuery('a.action-to-popover').click(popoveraddtorules);


}

jQuery(document).ready(popoverReady);

(function ($) {
$(function () {
	var $size_js = $("#popover-usejs-size"),
		$pos_js = $("#popover-usejs-position"),
		$all_js = $("#popoverusejs")
	;
	if (!$size_js.length || !$pos_js.length || !$all_js.length) return false;
	
	var check_size = function () {
		var enabled = $size_js.is(":checked");
		$("#popoverwidth,#popoverheight").attr("disabled", enabled)
	}
	$size_js.on("change", check_size);
	check_size();

	var check_pos = function () {
		var enabled = $pos_js.is(":checked");
		$("#popoverleft,#popovertop").attr("disabled", enabled)
	}
	$pos_js.on("change", check_pos);
	check_pos();

	var check_all = function () {
		var enabled = $all_js.is(":checked");
		$("#popoverwidth,#popoverheight,#popoverleft,#popovertop").attr("disabled", enabled)
	}
	$all_js.on("change", check_all);
	check_all();

});
})(jQuery);