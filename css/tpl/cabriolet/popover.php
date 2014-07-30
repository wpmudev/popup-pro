<div id='<?php echo $popover_messagebox; ?>' class='visiblebox' style='<?php echo $style; ?>'>
	<a href='' id='closebox' title='<?php _e('Close this box','popover'); ?>'></a>
	<div id='message' style='<?php echo $box; ?>'>
		<?php echo do_shortcode($popover_content); ?>
           
		<div class='clear'></div>
		<?php if($popover_hideforever != 'yes') {
			?>
			<div class='claimbutton hide'><a href='#' id='clearforever'><?php _e('Never see this message again.','popover'); ?></a></div>
			<?php
		}
		?>
	</div>
	<div class='clear'></div>
</div>