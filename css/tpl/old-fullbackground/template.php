<?php
/**
 *  File is included inside an IncPopupItem object.
 *  All variables of the object are available in this template.
 */

$msg_class = '';
// Compatibility mode to keep formatting of old PopUps (not used in new styles)
$content = stripslashes( $this->content );
$msg_class .= 'wdpu-' . $this->id . ' ';

if ( defined( 'PO_ALLOW_CONTENT_FILTERING' ) && PO_ALLOW_CONTENT_FILTERING ) {
	$content = defined( 'PO_USE_FULL_CONTENT_FILTERING' ) && PO_USE_FULL_CONTENT_FILTERING
		? apply_filters( 'the_content', stripslashes( $content ) )
		: wptexturize( wpautop( $content ) );
}

?>
<div id="darkbackground" class="wdpu-background" style="display: none">
<div id="<?php echo esc_attr( $this->code->id ); ?>"
	class="<?php echo esc_attr( $msg_class ); ?>"
	style="left: -1000px; top: 100px;">

	<a href="#" class="wdpu-close" title="<?php _e( 'Close this box', PO_LANG ); ?>"></a>
	<div id="message" class="wdpu-msg resize" style="<?php echo esc_attr( $this->code->colors ); ?>">

		<?php echo '' . do_shortcode( $content ); ?>

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
</div>