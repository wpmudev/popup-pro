<?php
/**
 * Metabox "submitdiv"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

?>
<div class="submitbox" id="submitpost">
	<div id="minor-publishing">
		<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
		<div style="display:none;">
			<?php submit_button( __( 'Save', PO_LANG ), 'button', 'save', false ); ?>
		</div>

		<div class="sticky-actions" style="display:none">
			<div class="delete-action">
			<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
				<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>">
				<?php _e( 'Move to Trash', PO_LANG ); ?>
				</a>
			<?php endif; ?>
			</div>

			<div class="publishing-action">
				<?php if ( 'inactive' === $popup->status ) : ?>
					<button class="button-primary"><?php _e( 'Activate', PO_LANG ); ?></button>
				<?php else : ?>
					<button class="button-primary"><?php _e( 'Save', PO_LANG ); ?></button>
				<?php endif; ?>
			</div>

			<div class="save-action">
				<?php if ( 'inactive' === $popup->status ) : ?>
					<button name="save" class="button"><?php _e( 'Save', PO_LANG ); ?></button>
				<?php else : ?>
					<button name="save" class="button"><?php _e( 'Deactivate', PO_LANG ); ?></button>
				<?php endif; ?>
			</div>

			<div class="preview-action">
				<span class="spinner"></span>
				<?php if ( ! empty( $popup->id ) ) : ?>
					<button type="button" class="preview button"><?php _e( 'Preview', PO_LANG ); ?></button>
				<?php endif; ?>
			</div>

			<div class="clear"></div>
		</div>

		<div id="minor-publishing-actions" class="non-sticky">
			<div class="save-action">
				<?php if ( 'inactive' === $popup->status ) : ?>
					<button name="save" id="save-post" class="button"><?php _e( 'Save', PO_LANG ); ?></button>
				<?php else : ?>
					<button name="save" id="save-post" class="button"><?php _e( 'Deactivate', PO_LANG ); ?></button>
				<?php endif; ?>
				<span class="spinner"></span>
			</div>
			<div class="preview-action">
				<?php if ( ! empty( $popup->id ) ) : ?>
					<button type="button" class="preview button"><?php _e( 'Preview', PO_LANG ); ?></button>
				<?php endif; ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<div id="misc-publishing-actions" class="non-sticky">
		<div class="misc-pub-section misc-pub-post-status status-<?php echo esc_attr( $popup->status ); ?>">
			<label for="post_status"><?php _e( 'Status:', PO_LANG ); ?></label>
			<span id="post-status-display">
				<?php echo esc_html( $popup->status_label( $popup->status ) ); ?>
				<i class="status-icon dashicons" style="display:none"></i>
			</span>
		</div><!-- .misc-pub-section -->
	</div>

	<div id="major-publishing-actions" class="non-sticky">
		<div class="delete-action">
		<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
			<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>">
			<?php _e( 'Move to Trash', PO_LANG ); ?>
			</a>
		<?php endif; ?>
		</div>

		<div class="publishing-action">
			<span class="spinner"></span>
			<?php if ( 'inactive' === $popup->status ) : ?>
				<button class="button-primary" id="publish"><?php _e( 'Activate', PO_LANG ); ?></button>
			<?php else : ?>
				<button class="button-primary" id="publish"><?php _e( 'Save', PO_LANG ); ?></button>
			<?php endif; ?>
		</div>

		<div class="clear"></div>
	</div>
</div>