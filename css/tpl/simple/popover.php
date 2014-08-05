<?php
/**
 *  File is included inside an IncPopupItem object.
 *  All variables of the object are available in this template.
 */

?>
<div id="<?php echo esc_attr( $this->code->id ); ?>"
	class="wdpu-container <?php ?>"
	style="display: none;">

	<div class="wdpu-holder">
		<div class="wdpu-wrap">
			<a href="#" class="wdpu-close" title="<?php _e( 'Close this box', PO_LANG ); ?>"></a>

			<div class="wdpu-msg resize no-move">
				<div class="wdpu-text">
					<?php echo '' . apply_filters( 'the_content', $this->content ); ?>
				</div>

				<div class="wdpu-image">
					...
				</div>

				<div class="clear"></div>
				<?php if ( $this->can_hide ) : ?>
					<div class="claimbutton hide">
						<a href="#" class="wdpu-hide-forever">
							<?php _e( 'Never see this message again.', PO_LANG ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>