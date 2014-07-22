function sizeReady() {
	jQuery(popover.messagebox).width(jQuery('#message').width());
	jQuery(popover.messagebox).height(jQuery('#message').height());

	jQuery(popover.messagebox).css('top', (jQuery(window).height() / 2) - (jQuery('#message').height() / 2) );
	jQuery(popover.messagebox).css('left', (jQuery(window).width() / 2) - (jQuery('#message').width() / 2) );
}

jQuery(window).load(sizeReady);