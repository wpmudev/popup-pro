<?php
/**
 *  File is included inside an IncPopupItem object.
 *  All variables of the object are available in this template.
 */

$has_title = ! empty( $this->title );
$has_subtitle = ! empty( $this->subtitle );
$has_cta = ! empty( $this->cta_label ) && ! empty( $this->cta_link );
$has_img = ! empty( $this->image );
$has_buttons = $has_cta || $this->can_hide;

$msg_class = '';
if ( $has_img ) {
	$msg_class .= 'img-' . $this->image_pos . ' ';
} else {
	$msg_class .= 'no-img ';
}
if ( $this->round_corners ) { $msg_class .= 'rounded '; }

?>
<div id="<?php echo esc_attr( $this->code->id ); ?>"
	class="wdpu-container <?php echo esc_attr( $msg_class ); ?>"
	style="display: none;">

	<div class="wdpu-holder wdpu-background">
		<div class="wdpu-wrap">
			<a href="#" class="wdpu-close" title="<?php _e( 'Close this box', PO_LANG ); ?>"></a>

			<div class="wdpu-msg resize no-move">
				<div class="wdpu-head">
					<?php if ( $has_title ) : ?>
						<div class="wdpu-title">
							<?php echo esc_html( $this->title ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $has_subtitle ) : ?>
						<div class="wdpu-subtitle">
							<?php echo esc_html( $this->subtitle ); ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="wdpu-middle">
					<div class="wdpu-text">
						<div class="wdpu-inner <?php if ( ! $has_buttons ) { echo esc_attr( 'no-bm' ); } ?>">
							<div class="wdpu-content">
								<?php echo '' . apply_filters( 'the_content', $this->content ); ?>
							</div>
						</div>
					</div>

					<?php if ( $has_img ) : ?>
						<div class="wdpu-image">
							<img src="<?php echo esc_url( $this->image ); ?>" />
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $has_buttons ) : ?>
					<div class="wdpu-buttons">
						<?php if ( $this->can_hide ) : ?>
						<a href="#" class="wdpu-hide-forever">
							<?php _e( 'Never see this message again.', PO_LANG ); ?>
						</a>
						<?php endif; ?>

						<?php if ( $has_cta ) : ?>
							<a href="<?php echo esc_url( $this->cta_link ); ?>" class="wdpu-cta">
								<?php echo esc_html( $this->cta_label ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>