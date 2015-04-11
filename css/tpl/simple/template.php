<?php
/**
 *  File is included inside an IncPopupItem object.
 *  All variables of the object are available in this template.
 *
 *  Placeholders:
 *    %color1%   .. PopUp Color-1
 *    %color2%   .. PopUp Color-2
 *    %id%   .. Random PopUp-ID (changes on every request)
 *    %title%   .. PopUp Title
 *    %subtitle%   .. PopUp Subtitle
 *    %img_url%   .. Feature-Image URL
 *    %content%   .. PopUp Contents (with parsed shortcodes)
 *    %cta_button%   .. Full CTA button tag
 *    %hide_forever%   .. Full Hide-Forver button tag
 *    %outer_class%   .. CSS classes (contains style-name and popup-ID)
 *    %inner_class%   .. CSS classes
 *    %outer_style%   .. CSS style tag contents
 *    %inner_style%   .. CSS style tag contents
 *
 *  Additional Variables
 *    $has_title   .. Bool
 *    $has_subtitle   .. Bool
 *    $has_cta   .. Bool
 *    $img_is_left   .. Bool
 *    $img_is_right   .. Bool
 *    $has_buttons   .. Bool
 */

?>
<div id="%id%" class="%outer_class%" style="%outer_style%">
	<div class="resize %inner_class%" style="%inner_style%">

		<a href="#" class="wdpu-close" title="<?php _e( 'Close this box', PO_LANG ); ?>"></a>

		<div class="wdpu-msg-inner resize">
			<?php if ( $img_is_left ) : ?>
			<div class="wdpu-image"><img src="%img_url%" /></div>
			<?php endif; ?>

			<div class="wdpu-text">
				<div class="wdpu-inner <?php if ( ! $has_buttons ) { echo esc_attr( 'no-bm' ); } ?>">
					<div class="wdpu-head">
						<div class="wdpu-title">%title%</div>
						<div class="wdpu-subtitle">%subtitle%</div>
					</div>
					<div class="wdpu-content">%content%</div>
				</div>

				<?php if ( $has_buttons ) : ?>
					<div class="wdpu-buttons">
						%cta_button%
						%hide_forever%
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $img_is_right ) : ?>
			<div class="wdpu-image"><img src="%img_url%" /></div>
			<?php endif; ?>
		</div>

	</div>
</div>