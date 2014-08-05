<?php
/**
 *  File is included inside an IncPopupItem object.
 *  All variables of the object are available in this template.
 */

?>
<div id="<?php echo esc_attr( $this->code->id ); ?>"
	class="visiblebox"
	style="z-index: 999999; left: -1000px; top: 100px; display: none;">

	<a href="#" class="wdpu-clise" title="<?php _e( 'Close this box', PO_LANG ); ?>"></a>
	<div id="message" style="<?php echo esc_attr( $this->code->colors ); ?>">

		<?php echo apply_filters( 'the_content', $this->content ); ?>

		<div class="clear"></div>
		<?php if ( $this->can_hide ) : ?>
			<div class="claimbutton hide">
				<a href="#" class="wdpu-hide-forever">
					<?php _e( 'Never see this message again.', PO_LANG ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<div class="clear"></div>

</div>