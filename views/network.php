<?php
/**
 * This page is only displayed when
 * 1. PO_GLOBAL is true
 * 2. The site is a Multisite network
 * 3. User is in the Network-Dashboard
 * 4. Any "PopUp" menuitem is opened (Edit PopUp, Create PopUp, ...)
 */

switch_to_blog( BLOG_ID_CURRENT_SITE );
$main_url = admin_url( 'edit.php?post_type=' . IncPopupItem::POST_TYPE );
$blog_title = get_bloginfo( 'name' );
restore_current_blog();

$dismiss_url = add_query_arg( 'popup_network', 'hide' );

?>
<style>
blockquote p {
	font-size: 19px;
	font-style: italic;
	font-weight: 300;
	background: #FAFAFA;
	padding: 10px;
}
</style>
<div id="wpbody-content" tabindex="0">
	<div class="wrap">
		<h2><?php _e( 'Global PopUps', PO_LANG ); ?></h2>

		<blockquote>
		<p><?php printf(
			__(
				'Please note:<br />We moved the global PopUp menu items ' .
				'to the <strong>Main Blog</strong> of your multisite ' .
				'network!<br />The Main Blog of this network is "%1$s" - ' .
				'<a href="%2$s">Go to the Main Blog now</a>!', PO_LANG
			),
			$blog_title,
			esc_url( $main_url )
		); ?></p>
		</blockquote>

		<div>
			<p><?php _e(
				'Because the "PopUp" menu items here on the ' .
				'<strong>Network Admin</strong> are not used anymore ' .
				'you can <strong>hide them</strong> at any time:', PO_LANG
				); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $dismiss_url ); ?>" class="button-primary">
					<?php _e( 'Hide the menu items here!', PO_LANG ); ?>
				</a>
			</p>
		</div>
	</div>
	<div class="clear"></div>
</div>